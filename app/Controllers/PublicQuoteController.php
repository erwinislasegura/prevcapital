<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Quote;
use App\Support\Mailer;
use App\Support\QuotePdf;

final class PublicQuoteController extends Controller
{
    public function show(): void
    {
        $quote = Quote::findByToken(trim((string) ($_GET['token'] ?? '')));
        if (!$quote) {
            http_response_code(404);
            $this->render('errors/404', [], 'public');
            return;
        }
        Quote::addEvent((int) $quote['id'], 'viewed', 'Cotización consultada desde el enlace público.');
        $this->render('site/quote', [
            'pageTitle' => 'Cotización ' . $quote['quote_number'],
            'metaDescription' => 'Cotización de servicios PrevCapital.',
            'activePage' => '',
            'quote' => $quote,
        ], 'public');
    }

    public function pdf(): void
    {
        $quote = Quote::findByToken(trim((string) ($_GET['token'] ?? '')));
        if (!$quote) {
            http_response_code(404);
            return;
        }
        Quote::addEvent((int) $quote['id'], 'pdf_downloaded', 'PDF descargado desde la cotización pública.');
        $pdf = QuotePdf::render($quote);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Cotizacion-' . $quote['quote_number'] . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
    }

    public function respond(): void
    {
        $token = trim((string) ($_POST['token'] ?? ''));
        $quote = Quote::findByToken($token);
        if (!$quote || !Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'No fue posible validar la respuesta. Actualice la página e intente nuevamente.');
            $this->redirect('/cotizacion?token=' . urlencode($token));
        }
        $decision = (string) ($_POST['decision'] ?? '');
        $reason = trim((string) ($_POST['reason'] ?? '')) ?: null;
        if ($decision === 'rejected' && $reason !== null && mb_strlen($reason) > 1000) {
            flash('error', 'El motivo no puede superar los 1.000 caracteres.');
            $this->redirect('/cotizacion?token=' . urlencode($token));
        }
        if (!Quote::respond((int) $quote['id'], $decision, $reason)) {
            flash('error', 'Esta cotización ya fue respondida o todavía no ha sido enviada.');
        } else {
            $updated = Quote::find((int) $quote['id']) ?? $quote;
            Mailer::sendDecisionNotification($updated, $decision);
            flash('success', $decision === 'accepted' ? 'Cotización aceptada. Gracias por confiar en PrevCapital.' : 'Respuesta registrada. Nos pondremos en contacto para revisar sus observaciones.');
        }
        $this->redirect('/cotizacion?token=' . urlencode($token));
    }
}
