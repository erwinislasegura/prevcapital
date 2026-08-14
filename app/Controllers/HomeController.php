<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\ContactInquiry;
use App\Support\Schema;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->renderPage(
            'site/home',
            'PrevCapital Empresas | Seguridad y Salud en el Trabajo',
            'Asesoría preventiva y blindaje legal para evitar demandas, indemnizaciones y riesgos que afectan la continuidad de su empresa.',
            'inicio',
            ['showCampaignPopup' => true]
        );
    }

    public function services(): void
    {
        $this->renderPage(
            'site/services',
            'Servicios de Prevención de Riesgos para Empresas | PrevCapital',
            'Diagnóstico preventivo, asesoría técnica, capacitaciones, carpetas de arranque, ISO 45001 y soluciones de seguridad laboral para empresas.',
            'servicios'
        );
    }

    public function compliance(): void
    {
        $this->renderPage(
            'site/compliance',
            'DS N°44 y Cumplimiento Normativo | PrevCapital',
            'Implementación DS N°44, MIPER, protocolos MINSAL, regularización de observaciones y preparación frente a fiscalizaciones.',
            'cumplimiento'
        );
    }

    public function about(): void
    {
        $this->renderPage(
            'site/about',
            'Nosotros y Metodología | PrevCapital',
            'Conozca el equipo, la metodología y el enfoque técnico de PrevCapital para proteger personas y sostener la continuidad operacional.',
            'nosotros'
        );
    }

    public function contact(): void
    {
        $this->renderPage(
            'site/contact',
            'Contacto y Evaluación Preventiva | PrevCapital',
            'Solicite una evaluación preventiva para su empresa y reciba orientación sobre DS N°44, protocolos MINSAL, ISO 45001 y gestión de riesgos.',
            'contacto'
        );
    }

    public function contactStore(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'La sesión expiró. Actualice la página e intente nuevamente.');
            $this->redirect('/contacto#contacto');
        }
        $now = time();
        $attempts = array_values(array_filter((array) ($_SESSION['contact_attempts'] ?? []), static fn ($time): bool => (int) $time > $now - 600));
        if (count($attempts) >= 3) {
            flash('error', 'Ha enviado varias solicitudes recientemente. Espere unos minutos antes de volver a intentar.');
            $this->redirect('/contacto#contacto');
        }
        $data = [
            'name' => trim((string) ($_POST['nombre'] ?? '')),
            'company' => trim((string) ($_POST['empresa'] ?? '')),
            'email' => mb_strtolower(trim((string) ($_POST['correo'] ?? ''))),
            'phone' => trim((string) ($_POST['telefono'] ?? '')) ?: null,
            'worker_count' => (int) ($_POST['numero_trabajadores'] ?? 0),
            'service' => trim((string) ($_POST['servicio'] ?? '')),
            'message' => trim((string) ($_POST['mensaje'] ?? '')) ?: null,
            'source' => 'Formulario web',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ];
        $errors = [];
        if (mb_strlen($data['name']) < 3 || mb_strlen($data['name']) > 140) $errors[] = 'Ingrese un nombre válido.';
        if (mb_strlen($data['company']) < 2 || mb_strlen($data['company']) > 160) $errors[] = 'Ingrese el nombre de la empresa.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 180) $errors[] = 'Ingrese un correo electrónico válido.';
        if ($data['worker_count'] < 1 || $data['worker_count'] > 1000000) $errors[] = 'Ingrese un número de trabajadores válido.';
        if ($data['service'] === '' || mb_strlen($data['service']) > 140) $errors[] = 'Seleccione el servicio que necesita.';
        if ($data['message'] !== null && mb_strlen($data['message']) > 3000) $errors[] = 'El mensaje no puede superar los 3.000 caracteres.';
        if ($errors) {
            flash('errors', $errors);
            flash('old', $_POST);
            $this->redirect('/contacto#contacto');
        }
        try {
            Schema::install();
            ContactInquiry::create($data);
            $attempts[] = $now;
            $_SESSION['contact_attempts'] = $attempts;
            flash('success', 'Gracias. Recibimos su solicitud y nos pondremos en contacto.');
        } catch (\Throwable) {
            flash('error', 'No fue posible registrar su solicitud. Intente nuevamente o escríbanos a contacto@prevcapital.cl.');
            flash('old', $_POST);
        }
        $this->redirect('/contacto#contacto');
    }

    private function renderPage(string $view, string $title, string $description, string $activePage, array $extra = []): void
    {
        $this->render($view, array_merge([
            'pageTitle' => $title,
            'metaDescription' => $description,
            'activePage' => $activePage,
        ], $extra), 'public');
    }
}
