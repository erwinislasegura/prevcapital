<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Models\Quote;
use App\Support\Mailer;
use App\Support\QuotePdf;

final class QuoteController extends Controller
{
    public function index(): void
    {
        $status = trim((string) ($_GET['status'] ?? ''));
        $search = trim((string) ($_GET['search'] ?? ''));
        $this->render('admin/quotes/index', [
            'pageTitle' => 'Cotizaciones',
            'quotes' => Quote::all($status, $search),
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): void
    {
        $this->render('admin/quotes/form', [
            'pageTitle' => 'Nueva cotización',
            'quote' => null,
            'defaultTerms' => 'Valores expresados en pesos chilenos. Vigencia según fecha indicada. El inicio del servicio se coordina una vez aceptada la propuesta.',
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf('/admin/cotizaciones/crear');
        [$data, $items, $errors] = $this->validatedData();
        if ($errors) $this->validationRedirect('/admin/cotizaciones/crear', $errors);
        $id = Quote::create($data, $items, Auth::id());
        AuditLog::record('quotes.created', 'quote', $id, 'Cotización creada.');
        $sendNow = isset($_POST['send_now']);
        if ($sendNow) {
            $this->sendQuote($id, '/admin/cotizaciones/ver?id=' . $id);
        }
        if (!$sendNow) flash('success', 'Cotización creada correctamente.');
        $this->redirect('/admin/cotizaciones/ver?id=' . $id);
    }

    public function show(): void
    {
        $quote = $this->quoteOrRedirect();
        $this->render('admin/quotes/show', ['pageTitle' => 'Cotización ' . $quote['quote_number'], 'quote' => $quote]);
    }

    public function edit(): void
    {
        $quote = $this->quoteOrRedirect();
        if (in_array($quote['status'], ['accepted', 'rejected'], true)) {
            flash('error', 'Una cotización respondida no puede ser modificada.');
            $this->redirect('/admin/cotizaciones/ver?id=' . $quote['id']);
        }
        $this->render('admin/quotes/form', ['pageTitle' => 'Editar ' . $quote['quote_number'], 'quote' => $quote, 'defaultTerms' => '']);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $quote = Quote::find($id);
        if (!$quote) $this->redirect('/admin/cotizaciones');
        if (in_array($quote['status'], ['accepted', 'rejected'], true)) {
            flash('error', 'Una cotización respondida no puede ser modificada.');
            $this->redirect('/admin/cotizaciones/ver?id=' . $id);
        }
        $this->validateCsrf('/admin/cotizaciones/editar?id=' . $id);
        [$data, $items, $errors] = $this->validatedData();
        if ($errors) $this->validationRedirect('/admin/cotizaciones/editar?id=' . $id, $errors);
        Quote::update($id, $data, $items, Auth::id());
        AuditLog::record('quotes.updated', 'quote', $id, 'Cotización actualizada.');
        flash('success', 'Cotización actualizada correctamente.');
        $this->redirect('/admin/cotizaciones/ver?id=' . $id);
    }

    public function pdf(): void
    {
        $quote = $this->quoteOrRedirect();
        $pdf = QuotePdf::render($quote);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Cotizacion-' . $quote['quote_number'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    }

    public function send(): void
    {
        $this->validateCsrf('/admin/cotizaciones');
        $id = (int) ($_POST['id'] ?? 0);
        $this->sendQuote($id, '/admin/cotizaciones/ver?id=' . $id);
        $this->redirect('/admin/cotizaciones/ver?id=' . $id);
    }

    public function delete(): void
    {
        $this->validateCsrf('/admin/cotizaciones');
        $id = (int) ($_POST['id'] ?? 0);
        $quote = Quote::find($id);
        if (!$quote) {
            flash('error', 'La cotización indicada no existe.');
        } elseif (in_array($quote['status'], ['accepted', 'rejected'], true)) {
            flash('error', 'No se puede eliminar una cotización que ya fue respondida.');
        } else {
            Quote::delete($id);
            AuditLog::record('quotes.deleted', 'quote', $id, 'Cotización eliminada.');
            flash('success', 'Cotización eliminada correctamente.');
        }
        $this->redirect('/admin/cotizaciones');
    }

    private function sendQuote(int $id, string $fallback): void
    {
        $quote = Quote::find($id);
        if (!$quote) {
            flash('error', 'La cotización indicada no existe.');
            $this->redirect('/admin/cotizaciones');
        }
        if (in_array($quote['status'], ['accepted', 'rejected'], true)) {
            flash('error', 'La cotización ya fue respondida y no puede reenviarse.');
            $this->redirect($fallback);
        }
        try {
            if (!Mailer::sendQuote($quote, QuotePdf::render($quote))) {
                flash('error', 'El servidor de correo no confirmó el envío. Revise la configuración MAIL_* de cPanel.');
                $this->redirect($fallback);
            }
            Quote::markSent($id);
            AuditLog::record('quotes.sent', 'quote', $id, 'Cotización enviada a ' . $quote['email'] . '.');
            flash('success', 'Cotización enviada con el PDF y enlace de respuesta.');
        } catch (\Throwable) {
            flash('error', 'No fue posible enviar el correo. Revise la cuenta de correo configurada en el servidor.');
        }
    }

    private function validatedData(): array
    {
        $data = [
            'client_name' => trim((string) ($_POST['client_name'] ?? '')),
            'company' => trim((string) ($_POST['company'] ?? '')),
            'email' => mb_strtolower(trim((string) ($_POST['email'] ?? ''))),
            'phone' => trim((string) ($_POST['phone'] ?? '')) ?: null,
            'tax_id' => trim((string) ($_POST['tax_id'] ?? '')) ?: null,
            'address' => trim((string) ($_POST['address'] ?? '')) ?: null,
            'subject' => trim((string) ($_POST['subject'] ?? '')),
            'issue_date' => trim((string) ($_POST['issue_date'] ?? '')),
            'valid_until' => trim((string) ($_POST['valid_until'] ?? '')),
            'currency' => 'CLP',
            'tax_rate' => $this->decimal($_POST['tax_rate'] ?? 19),
            'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
            'terms' => trim((string) ($_POST['terms'] ?? '')) ?: null,
        ];
        $errors = [];
        if (mb_strlen($data['client_name']) < 3 || mb_strlen($data['client_name']) > 140) $errors[] = 'Ingrese el nombre del contacto.';
        if (mb_strlen($data['company']) < 2 || mb_strlen($data['company']) > 160) $errors[] = 'Ingrese la empresa del cliente.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Ingrese un correo electrónico válido.';
        if (mb_strlen($data['subject']) < 3 || mb_strlen($data['subject']) > 200) $errors[] = 'Ingrese el asunto de la cotización.';
        if (!$this->validDate($data['issue_date']) || !$this->validDate($data['valid_until'])) $errors[] = 'Ingrese fechas válidas.';
        if ($this->validDate($data['issue_date']) && $this->validDate($data['valid_until']) && $data['valid_until'] < $data['issue_date']) $errors[] = 'La vigencia no puede terminar antes de la fecha de emisión.';
        if ($data['tax_rate'] < 0 || $data['tax_rate'] > 100) $errors[] = 'La tasa de impuesto debe estar entre 0 y 100.';

        $descriptions = (array) ($_POST['item_description'] ?? []);
        $details = (array) ($_POST['item_detail'] ?? []);
        $quantities = (array) ($_POST['item_quantity'] ?? []);
        $prices = (array) ($_POST['item_unit_price'] ?? []);
        $items = [];
        foreach ($descriptions as $index => $description) {
            $description = trim((string) $description);
            if ($description === '') continue;
            $quantity = $this->decimal($quantities[$index] ?? 0);
            $price = $this->decimal($prices[$index] ?? 0);
            if ($quantity <= 0) $errors[] = 'Cada partida debe tener una cantidad mayor que cero.';
            if ($price < 0) $errors[] = 'El valor unitario no puede ser negativo.';
            if (mb_strlen($description) > 220) $errors[] = 'La descripción de una partida es demasiado extensa.';
            $items[] = [
                'description' => $description,
                'detail' => trim((string) ($details[$index] ?? '')) ?: null,
                'quantity' => $quantity,
                'unit_price' => $price,
                'total' => round($quantity * $price, 2),
            ];
        }
        if (!$items) $errors[] = 'Agregue al menos una partida a la cotización.';
        $subtotal = array_sum(array_column($items, 'total'));
        $taxAmount = round($subtotal * ($data['tax_rate'] / 100), 2);
        $data['subtotal'] = $subtotal;
        $data['tax_amount'] = $taxAmount;
        $data['total'] = $subtotal + $taxAmount;
        return [$data, $items, array_values(array_unique($errors))];
    }

    private function decimal(mixed $value): float
    {
        $value = trim((string) $value);
        if (str_contains($value, ',') && str_contains($value, '.')) $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }

    private function validDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function quoteOrRedirect(): array
    {
        $quote = Quote::find((int) ($_GET['id'] ?? 0));
        if (!$quote) {
            flash('error', 'La cotización indicada no existe.');
            $this->redirect('/admin/cotizaciones');
        }
        return $quote;
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
