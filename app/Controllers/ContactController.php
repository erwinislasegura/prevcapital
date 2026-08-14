<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Models\ContactInquiry;

final class ContactController extends Controller
{
    public function index(): void
    {
        $status = trim((string) ($_GET['status'] ?? ''));
        $search = trim((string) ($_GET['search'] ?? ''));
        $this->render('admin/contacts/index', [
            'pageTitle' => 'Solicitudes de contacto',
            'contacts' => ContactInquiry::all($status, $search),
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function show(): void
    {
        $contact = ContactInquiry::find((int) ($_GET['id'] ?? 0));
        if (!$contact) {
            flash('error', 'La solicitud indicada no existe.');
            $this->redirect('/admin/contactos');
        }
        $this->render('admin/contacts/show', ['pageTitle' => 'Solicitud de contacto', 'contact' => $contact]);
    }

    public function status(): void
    {
        $this->validateCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');
        if (!ContactInquiry::find($id) || !in_array($status, ['new', 'contacted', 'closed'], true)) {
            flash('error', 'No fue posible actualizar la solicitud.');
        } else {
            ContactInquiry::updateStatus($id, $status, Auth::id());
            AuditLog::record('contacts.status_changed', 'contact_inquiry', $id, 'Estado actualizado a ' . $status . '.');
            flash('success', 'Estado de la solicitud actualizado.');
        }
        $this->redirect('/admin/contactos/ver?id=' . $id);
    }

    public function delete(): void
    {
        $this->validateCsrf();
        $id = (int) ($_POST['id'] ?? 0);
        if (ContactInquiry::find($id)) {
            ContactInquiry::delete($id);
            AuditLog::record('contacts.deleted', 'contact_inquiry', $id, 'Solicitud eliminada.');
            flash('success', 'Solicitud eliminada correctamente.');
        }
        $this->redirect('/admin/contactos');
    }

    private function validateCsrf(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'La sesión expiró. Intente nuevamente.');
            $this->redirect('/admin/contactos');
        }
    }
}
