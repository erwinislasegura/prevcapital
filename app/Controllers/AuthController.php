<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Support\Schema;

final class AuthController extends Controller
{
    public function show(): void
    {
        if (Auth::check()) {
            $this->redirect('/admin');
        }
        $this->render('auth/login', [], 'auth');
    }

    public function login(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'La sesión expiró. Intente nuevamente.');
            $this->redirect('/login');
        }
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        try {
            Schema::install();
            if (!Auth::attempt($email, $password)) {
                flash('error', 'Correo o contraseña incorrectos, o usuario inactivo.');
                flash('old', ['email' => $email]);
                $this->redirect('/login');
            }
        } catch (\Throwable) {
            flash('error', 'El sistema aún no está configurado. Complete la instalación inicial.');
            $this->redirect('/setup');
        }

        AuditLog::record('auth.login', 'user', Auth::id(), 'Inicio de sesión exitoso.');
        flash('success', 'Bienvenido al panel de PrevCapital.');
        $this->redirect('/admin');
    }

    public function logout(): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'No fue posible cerrar la sesión de forma segura.');
            $this->redirect('/admin');
        }
        AuditLog::record('auth.logout', 'user', Auth::id(), 'Cierre de sesión.');
        Auth::logout();
        flash('success', 'Sesión cerrada correctamente.');
        $this->redirect('/login');
    }
}
