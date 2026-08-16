<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\MarketingContact;

final class EmailSubscriptionController extends Controller
{
    public function show(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $this->render('site/unsubscribe', [
            'pageTitle' => 'Preferencias de correo | PrevCapital',
            'metaDescription' => 'Administrar preferencias de correo de PrevCapital.',
            'activePage' => '',
            'token' => $token,
            'contact' => MarketingContact::findByToken($token),
            'completed' => false,
        ], 'public');
    }

    public function store(): void
    {
        $token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));
        $completed = MarketingContact::unsubscribe($token);
        $this->render('site/unsubscribe', [
            'pageTitle' => 'Preferencias de correo | PrevCapital',
            'metaDescription' => 'Administrar preferencias de correo de PrevCapital.',
            'activePage' => '',
            'token' => $token,
            'contact' => MarketingContact::findByToken($token),
            'completed' => $completed,
        ], 'public');
    }
}
