<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Models\Client;

final class ClientController extends Controller
{
    public function index(): void
    {
        $search = trim((string) ($_GET['search'] ?? ''));
        $status = (string) ($_GET['status'] ?? '');
        $active = $status === 'active' ? true : ($status === 'inactive' ? false : null);
        $this->render('admin/clients/index', [
            'pageTitle' => 'Clientes',
            'clients' => Client::all($search, $active),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->render('admin/clients/form', ['pageTitle' => 'Nuevo cliente', 'client' => null]);
    }

    public function store(): void
    {
        $this->validateCsrf('/admin/clientes/crear');
        [$data, $errors] = $this->validatedData();
        if (Client::duplicateExists($data['email'], $data['company'])) $errors[] = 'Ya existe un cliente con la misma empresa y correo.';
        if ($errors) $this->validationRedirect('/admin/clientes/crear', $errors);
        $id = Client::create($data, Auth::id());
        AuditLog::record('clients.created', 'client', $id, 'Cliente creado desde el panel.');
        flash('success', 'Cliente creado y disponible para nuevas cotizaciones.');
        $this->redirect('/admin/clientes');
    }

    public function edit(): void
    {
        $client = Client::find((int) ($_GET['id'] ?? 0));
        if (!$client) {
            flash('error', 'El cliente indicado no existe.');
            $this->redirect('/admin/clientes');
        }
        $this->render('admin/clients/form', ['pageTitle' => 'Editar cliente', 'client' => $client]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!Client::find($id)) $this->redirect('/admin/clientes');
        $this->validateCsrf('/admin/clientes/editar?id=' . $id);
        [$data, $errors] = $this->validatedData();
        if (Client::duplicateExists($data['email'], $data['company'], $id)) $errors[] = 'Ya existe otro cliente con la misma empresa y correo.';
        if ($errors) $this->validationRedirect('/admin/clientes/editar?id=' . $id, $errors);
        Client::update($id, $data, Auth::id());
        AuditLog::record('clients.updated', 'client', $id, 'Datos del cliente actualizados.');
        flash('success', 'Cliente actualizado correctamente.');
        $this->redirect('/admin/clientes');
    }

    public function delete(): void
    {
        $this->validateCsrf('/admin/clientes');
        $id = (int) ($_POST['id'] ?? 0);
        if (Client::find($id)) {
            Client::delete($id);
            AuditLog::record('clients.deleted', 'client', $id, 'Cliente eliminado; las cotizaciones conservaron sus datos históricos.');
            flash('success', 'Cliente eliminado. Sus cotizaciones históricas se conservaron.');
        }
        $this->redirect('/admin/clientes');
    }

    private function validatedData(): array
    {
        $data = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'company' => trim((string) ($_POST['company'] ?? '')),
            'email' => mb_strtolower(trim((string) ($_POST['email'] ?? ''))),
            'phone' => trim((string) ($_POST['phone'] ?? '')) ?: null,
            'tax_id' => trim((string) ($_POST['tax_id'] ?? '')) ?: null,
            'address' => trim((string) ($_POST['address'] ?? '')) ?: null,
            'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
            'status' => isset($_POST['status']) ? 1 : 0,
        ];
        $errors = [];
        if (mb_strlen($data['name']) < 3 || mb_strlen($data['name']) > 140) $errors[] = 'Ingrese un nombre de contacto válido.';
        if (mb_strlen($data['company']) < 2 || mb_strlen($data['company']) > 160) $errors[] = 'Ingrese la empresa del cliente.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 180) $errors[] = 'Ingrese un correo electrónico válido.';
        if ($data['phone'] !== null && mb_strlen($data['phone']) > 60) $errors[] = 'El teléfono es demasiado extenso.';
        if ($data['notes'] !== null && mb_strlen($data['notes']) > 3000) $errors[] = 'Las notas no pueden superar los 3.000 caracteres.';
        return [$data, $errors];
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
