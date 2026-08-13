<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;

final class UserController extends Controller
{
    public function index(): void
    {
        $this->render('admin/users/index', ['pageTitle' => 'Usuarios', 'users' => User::all()]);
    }

    public function create(): void
    {
        $this->render('admin/users/form', [
            'pageTitle' => 'Nuevo usuario',
            'user' => null,
            'roles' => Role::all(),
            'selectedRoles' => array_map('intval', (array) old('role_ids', [])),
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf('/admin/users/create');
        [$data, $roleIds, $errors] = $this->validatedData();
        if (mb_strlen((string) ($_POST['password'] ?? '')) < 10) {
            $errors[] = 'La contraseña debe tener al menos 10 caracteres.';
        }
        if (User::emailExists($data['email'])) {
            $errors[] = 'El correo electrónico ya está registrado.';
        }
        if ($errors) {
            $this->validationRedirect('/admin/users/create', $errors);
        }
        $data['password'] = password_hash((string) $_POST['password'], PASSWORD_DEFAULT);
        $id = User::create($data, $roleIds);
        AuditLog::record('users.created', 'user', $id, 'Usuario creado desde el panel.');
        flash('success', 'Usuario creado correctamente.');
        $this->redirect('/admin/users');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = User::find($id);
        if (!$user) {
            flash('error', 'El usuario solicitado no existe.');
            $this->redirect('/admin/users');
        }
        $this->render('admin/users/form', [
            'pageTitle' => 'Editar usuario',
            'user' => $user,
            'roles' => Role::all(),
            'selectedRoles' => User::roleIds($id),
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = User::find($id);
        if (!$user) $this->redirect('/admin/users');
        $this->validateCsrf('/admin/users/edit?id=' . $id);
        [$data, $roleIds, $errors] = $this->validatedData();
        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '' && mb_strlen($password) < 10) {
            $errors[] = 'La nueva contraseña debe tener al menos 10 caracteres.';
        }
        if (User::emailExists($data['email'], $id)) {
            $errors[] = 'El correo electrónico ya está registrado.';
        }
        if ($id === Auth::id() && (int) $data['status'] !== 1) {
            $errors[] = 'No puede desactivar su propio usuario.';
        }
        $superadminRoleIds = array_map('intval', array_column(array_filter(Role::all(), static fn (array $role): bool => $role['slug'] === 'superadmin'), 'id'));
        $keepsSuperadmin = (bool) array_intersect($roleIds, $superadminRoleIds);
        if (User::isSuperadmin($id) && !$keepsSuperadmin && User::superadminCount() <= 1) {
            $errors[] = 'Debe existir al menos un superadministrador activo.';
        }
        if ($errors) {
            $this->validationRedirect('/admin/users/edit?id=' . $id, $errors);
        }
        $data['password'] = $password === '' ? null : password_hash($password, PASSWORD_DEFAULT);
        User::update($id, $data, $roleIds);
        AuditLog::record('users.updated', 'user', $id, 'Datos y roles del usuario actualizados.');
        flash('success', 'Usuario actualizado correctamente.');
        $this->redirect('/admin/users');
    }

    public function toggle(): void
    {
        $this->validateCsrf('/admin/users');
        $id = (int) ($_POST['id'] ?? 0);
        $user = User::find($id);
        if (!$user) {
            flash('error', 'El usuario solicitado no existe.');
            $this->redirect('/admin/users');
        }
        if ($id === Auth::id()) {
            flash('error', 'No puede cambiar el estado de su propio usuario.');
        } elseif ((int) $user['status'] === 1 && User::isSuperadmin($id) && User::superadminCount() <= 1) {
            flash('error', 'No puede desactivar el único superadministrador.');
        } else {
            User::toggle($id);
            AuditLog::record('users.status_changed', 'user', $id, 'Estado del usuario modificado.');
            flash('success', 'Estado del usuario actualizado.');
        }
        $this->redirect('/admin/users');
    }

    public function delete(): void
    {
        $this->validateCsrf('/admin/users');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === Auth::id()) {
            flash('error', 'No puede eliminar su propio usuario.');
        } elseif (User::isSuperadmin($id) && User::superadminCount() <= 1) {
            flash('error', 'No puede eliminar el único superadministrador.');
        } else {
            User::delete($id);
            AuditLog::record('users.deleted', 'user', $id, 'Usuario eliminado.');
            flash('success', 'Usuario eliminado correctamente.');
        }
        $this->redirect('/admin/users');
    }

    private function validatedData(): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $status = isset($_POST['status']) ? 1 : 0;
        $roleIds = User::validRoleIds((array) ($_POST['role_ids'] ?? []));
        $errors = [];
        if (mb_strlen($name) < 3) $errors[] = 'Ingrese un nombre de al menos 3 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ingrese un correo electrónico válido.';
        if (!$roleIds) $errors[] = 'Seleccione al menos un rol.';
        return [['name' => $name, 'email' => $email, 'status' => $status], $roleIds, $errors];
    }

    private function validateCsrf(string $fallback): void
    {
        if (!Csrf::validate($_POST['_token'] ?? null)) {
            flash('error', 'La sesión expiró. Intente nuevamente.');
            $this->redirect($fallback);
        }
    }

    private function validationRedirect(string $path, array $errors): never
    {
        flash('errors', $errors);
        flash('old', $_POST);
        $this->redirect($path);
    }
}
