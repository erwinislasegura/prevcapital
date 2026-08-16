<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Models\MarketingCampaign;
use App\Models\MarketingContact;
use App\Models\MarketingTemplate;
use App\Support\EmailDeliverability;
use App\Support\EmailTemplate;
use App\Support\MarketingQueue;
use App\Support\Schema;
use DateTimeImmutable;

final class MarketingController extends Controller
{
    public function index(): void
    {
        Schema::install();
        $this->render('admin/marketing/index', [
            'pageTitle' => 'Email Marketing',
            'campaigns' => MarketingCampaign::all(),
            'counts' => MarketingContact::counts(),
            'deliverability' => EmailDeliverability::report(),
        ]);
    }

    public function template(): void
    {
        Schema::install();
        $this->render('admin/marketing/template', [
            'pageTitle' => 'Plantilla de correo',
            'template' => MarketingTemplate::current(),
        ]);
    }

    public function updateTemplate(): void
    {
        $this->validateCsrf('/admin/email-marketing/plantilla');
        Schema::install();
        $template = MarketingTemplate::current();
        $name = trim((string) ($_POST['name'] ?? ''));
        $subject = trim((string) ($_POST['subject_default'] ?? ''));
        $html = EmailTemplate::sanitize((string) ($_POST['html_content'] ?? ''));
        $text = trim((string) ($_POST['text_content'] ?? ''));
        $errors = [];
        if (mb_strlen($name) < 3 || mb_strlen($name) > 120) $errors[] = 'Ingrese un nombre válido para la plantilla.';
        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 180) $errors[] = 'Ingrese un asunto base válido.';
        if (!str_contains($html, '{{contenido}}')) $errors[] = 'La plantilla HTML debe incluir {{contenido}}.';
        if (!str_contains($html, '{{unsubscribe_url}}')) $errors[] = 'La plantilla HTML debe incluir {{unsubscribe_url}}.';
        if (!str_contains($text, '{{contenido_texto}}') || !str_contains($text, '{{unsubscribe_url}}')) $errors[] = 'La versión de texto debe incluir {{contenido_texto}} y {{unsubscribe_url}}.';
        if ($errors) $this->validationRedirect('/admin/email-marketing/plantilla', $errors);
        MarketingTemplate::update((int) $template['id'], [
            'name' => $name, 'subject_default' => $subject, 'html_content' => $html, 'text_content' => $text,
        ], Auth::id());
        AuditLog::record('marketing.template_updated', 'marketing_template', (int) $template['id'], 'Plantilla de Email Marketing actualizada.');
        flash('success', 'Plantilla actualizada correctamente.');
        $this->redirect('/admin/email-marketing/plantilla');
    }

    public function create(): void
    {
        Schema::install();
        $this->render('admin/marketing/form', [
            'pageTitle' => 'Nueva campaña',
            'template' => MarketingTemplate::current(),
            'deliverabilityReady' => EmailDeliverability::readyForMarketing(),
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf('/admin/email-marketing/crear');
        Schema::install();
        $template = MarketingTemplate::current();
        $name = trim((string) ($_POST['name'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $contentHtml = EmailTemplate::sanitize((string) ($_POST['content_html'] ?? ''));
        $contentText = trim((string) ($_POST['content_text'] ?? ''));
        $scheduledInput = trim((string) ($_POST['scheduled_at'] ?? ''));
        $errors = [];
        if (!EmailDeliverability::readyForMarketing()) $errors[] = 'La campaña no puede activarse hasta validar SMTP, SPF, DKIM y DMARC.';
        if (!isset($_POST['consent_confirmed'])) $errors[] = 'Confirme que los destinatarios autorizaron comunicaciones comerciales.';
        if (mb_strlen($name) < 3 || mb_strlen($name) > 160) $errors[] = 'Ingrese un nombre interno para la campaña.';
        if (mb_strlen($subject) < 3 || mb_strlen($subject) > 180 || preg_match('/[\r\n]/', $subject)) $errors[] = 'Ingrese un asunto válido.';
        if (mb_strlen(strip_tags($contentHtml)) < 30) $errors[] = 'El contenido HTML es demasiado breve.';
        if (mb_strlen($contentText) < 30) $errors[] = 'Agregue una versión de texto del contenido.';
        $scheduled = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $scheduledInput) ?: null;
        if (!$scheduled) $errors[] = 'Ingrese una fecha y hora válidas.';
        if ($scheduled && $scheduled->getTimestamp() < time() - 60) $errors[] = 'La campaña no puede comenzar en una fecha pasada.';
        $contacts = $this->collectContacts($errors);
        if (!$contacts) $errors[] = 'Agregue al menos un destinatario suscrito y válido.';
        if (count($contacts) > 500) $errors[] = 'Por seguridad, cada campaña admite hasta 500 destinatarios.';
        $warnings = EmailTemplate::contentWarnings($subject, str_replace('{{contenido}}', $contentHtml, (string) $template['html_content']));
        if ($warnings && !isset($_POST['warnings_reviewed'])) {
            foreach ($warnings as $warning) $errors[] = $warning;
            $errors[] = 'Revise las advertencias y marque la confirmación antes de programar.';
        }
        if ($errors) $this->validationRedirect('/admin/email-marketing/crear', array_values(array_unique($errors)));
        $html = str_replace('{{contenido}}', $contentHtml, (string) $template['html_content']);
        $text = str_replace('{{contenido_texto}}', $contentText, (string) $template['text_content']);
        $id = MarketingCampaign::create([
            'name' => $name,
            'subject' => $subject,
            'html_content' => $html,
            'text_content' => $text,
            'scheduled_at' => $scheduled->format('Y-m-d H:i:s'),
            'interval_minutes' => 10,
        ], $contacts, Auth::id());
        AuditLog::record('marketing.campaign_created', 'marketing_campaign', $id, 'Campaña creada con ' . count($contacts) . ' destinatarios.');
        flash('success', 'Campaña programada. Los correos se enviarán individualmente con al menos 10 minutos de separación.');
        $this->redirect('/admin/email-marketing/ver?id=' . $id);
    }

    public function show(): void
    {
        Schema::install();
        $campaign = MarketingCampaign::find((int) ($_GET['id'] ?? 0));
        if (!$campaign) $this->redirect('/admin/email-marketing');
        $this->render('admin/marketing/show', ['pageTitle' => 'Campaña ' . $campaign['name'], 'campaign' => $campaign]);
    }

    public function toggle(): void
    {
        $this->validateCsrf('/admin/email-marketing');
        $id = (int) ($_POST['id'] ?? 0);
        $campaign = MarketingCampaign::find($id);
        if (!$campaign) $this->redirect('/admin/email-marketing');
        $pause = $campaign['status'] !== 'paused';
        MarketingCampaign::setPaused($id, $pause);
        AuditLog::record($pause ? 'marketing.campaign_paused' : 'marketing.campaign_resumed', 'marketing_campaign', $id, null);
        flash('success', $pause ? 'Campaña pausada.' : 'Campaña reanudada.');
        $this->redirect('/admin/email-marketing/ver?id=' . $id);
    }

    public function process(): void
    {
        $this->validateCsrf('/admin/email-marketing');
        try {
            $result = MarketingQueue::processNext();
            flash($result['status'] === 'sent' ? 'success' : 'error', $result['message']);
        } catch (\Throwable $exception) {
            flash('error', 'No fue posible procesar la cola: ' . $exception->getMessage());
        }
        $this->redirect('/admin/email-marketing');
    }

    private function collectContacts(array &$errors): array
    {
        $rows = MarketingContact::sourceRows(isset($_POST['include_clients']), isset($_POST['include_inquiries']));
        $manual = preg_split('/\r?\n/', (string) ($_POST['recipients'] ?? '')) ?: [];
        foreach ($manual as $lineNumber => $line) {
            $line = trim($line);
            if ($line === '') continue;
            $parts = array_map('trim', str_getcsv($line));
            if (count($parts) === 1) {
                $rows[] = ['email' => $parts[0], 'name' => null, 'company' => null, 'source' => 'Carga manual'];
            } else {
                $emailIndex = filter_var($parts[0], FILTER_VALIDATE_EMAIL) ? 0 : 1;
                $rows[] = ['email' => $parts[$emailIndex] ?? '', 'name' => $emailIndex === 0 ? ($parts[1] ?? null) : $parts[0], 'company' => $parts[2] ?? null, 'source' => 'Carga manual'];
            }
        }
        $contacts = [];
        foreach ($rows as $row) {
            $contact = MarketingContact::upsert((string) ($row['email'] ?? ''), $row['name'] ?? null, $row['company'] ?? null, (string) ($row['source'] ?? 'Campaña'));
            if (!$contact) {
                $errors[] = 'Se omitió una dirección de correo inválida en la lista de destinatarios.';
                continue;
            }
            if ($contact['status'] === 'subscribed') $contacts[mb_strtolower((string) $contact['email'])] = $contact;
        }
        return array_values($contacts);
    }

    private function validateCsrf(string $fallback): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'La sesión expiró. Intente nuevamente.');
            $this->redirect($fallback);
        }
    }

    private function validationRedirect(string $path, array $errors): void
    {
        flash('errors', $errors);
        flash('old', $_POST);
        $this->redirect($path);
    }
}
