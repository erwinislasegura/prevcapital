<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'pageTitle' => 'PrevCapital Empresas | Seguridad y Salud en el Trabajo',
            'metaDescription' => 'Consultoría estratégica en prevención de riesgos, implementación DS N°44, protocolos MINSAL, carpetas de arranque e ISO 45001 para empresas.',
            'showCampaignPopup' => true,
        ], 'public');
    }
}
