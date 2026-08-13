<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Support\Schema;

final class SetupController extends Controller
{
    public function show(): void
    {
        Schema::install();
        if (User::count() > 0) {
            $this->redirect('/login');
        }
        $this->render('setup/index', [], 'auth');
    }

    public function store(): void
    {
        Schema::install();
        if (User::count() > 0) {
            $this->redirect('/login');
        }
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'La sesión expiró. Intente nuevamente.');
            $this->redirect('/setup');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirmation'] ?? '');
        $errors = [];
        if (mb_strlen($name) < 3) $errors[] = 'Ingrese el nombre completo del administrador.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ingrese un correo electrónico válido.';
        if (mb_strlen($password) < 10) $errors[] = 'La contraseña debe tener al menos 10 caracteres.';
        if ($password !== $confirmation) $errors[] = 'Las contraseñas no coinciden.';

        if ($errors) {
            flash('errors', $errors);
            flash('old', ['name' => $name, 'email' => $email]);
            $this->redirect('/setup');
        }

        $superadmin = array_values(array_filter(Role::all(), static fn (array $role): bool => $role['slug'] === 'superadmin'))[0];
        $id = User::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 1,
        ], [(int) $superadmin['id']]);

        Auth::attempt($email, $password);
        AuditLog::record('system.installed', 'user', $id, 'Instalación inicial y creación del superadministrador.');
        flash('success', 'PrevCapital quedó configurado correctamente.');
        $this->redirect('/admin');
    }
}
