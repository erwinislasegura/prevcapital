<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->renderPage(
            'site/home',
            'PrevCapital Empresas | Seguridad y Salud en el Trabajo',
            'Consultoría estratégica en prevención de riesgos, implementación DS N°44, protocolos MINSAL, carpetas de arranque e ISO 45001 para empresas.',
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

    private function renderPage(string $view, string $title, string $description, string $activePage, array $extra = []): void
    {
        $this->render($view, array_merge([
            'pageTitle' => $title,
            'metaDescription' => $description,
            'activePage' => $activePage,
        ], $extra), 'public');
    }
}
