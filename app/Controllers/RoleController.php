<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;

final class RoleController extends Controller
{
    public function index(): void
    {
        $this->render('admin/roles/index', ['pageTitle' => 'Roles y permisos', 'roles' => Role::all()]);
    }

    public function create(): void
    {
        $this->render('admin/roles/form', [
            'pageTitle' => 'Nuevo rol', 'role' => null,
            'permissions' => Permission::grouped(), 'selectedPermissions' => [],
        ]);
    }

    public function store(): void
    {
        $this->validateCsrf('/admin/roles/create');
        [$data, $permissionIds, $errors] = $this->validatedData();
        if (Role::slugExists($data['slug'])) $errors[] = 'Ya existe un rol con ese nombre.';
        if ($errors) $this->validationRedirect('/admin/roles/create', $errors);
        $id = Role::create($data, $permissionIds);
        AuditLog::record('roles.created', 'role', $id, 'Rol creado y permisos asignados.');
        flash('success', 'Rol creado correctamente.');
        $this->redirect('/admin/roles');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $role = Role::find($id);
        if (!$role) {
            flash('error', 'El rol solicitado no existe.');
            $this->redirect('/admin/roles');
        }
        $this->render('admin/roles/form', [
            'pageTitle' => 'Editar rol', 'role' => $role,
            'permissions' => Permission::grouped(), 'selectedPermissions' => Role::permissionIds($id),
        ]);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $role = Role::find($id);
        if (!$role) $this->redirect('/admin/roles');
        $this->validateCsrf('/admin/roles/edit?id=' . $id);
        [$data, $permissionIds, $errors] = $this->validatedData();
        if ((int) $role['is_system'] === 1) $data['slug'] = $role['slug'];
        if (Role::slugExists($data['slug'], $id)) $errors[] = 'Ya existe un rol con ese nombre.';
        if ($errors) $this->validationRedirect('/admin/roles/edit?id=' . $id, $errors);
        Role::update($id, $data, $permissionIds);
        AuditLog::record('roles.updated', 'role', $id, 'Rol y permisos actualizados.');
        flash('success', 'Rol actualizado correctamente.');
        $this->redirect('/admin/roles');
    }

    public function delete(): void
    {
        $this->validateCsrf('/admin/roles');
        $id = (int) ($_POST['id'] ?? 0);
        if (!Role::delete($id)) {
            flash('error', 'El rol está protegido o tiene usuarios asignados y no puede eliminarse.');
        } else {
            AuditLog::record('roles.deleted', 'role', $id, 'Rol eliminado.');
            flash('success', 'Rol eliminado correctamente.');
        }
        $this->redirect('/admin/roles');
    }

    private function validatedData(): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $permissionIds = Permission::validIds((array) ($_POST['permission_ids'] ?? []));
        $errors = [];
        if (mb_strlen($name) < 3) $errors[] = 'Ingrese un nombre de al menos 3 caracteres.';
        if (!$permissionIds) $errors[] = 'Seleccione al menos un permiso.';
        return [['name' => $name, 'slug' => slugify($name), 'description' => $description ?: null], $permissionIds, $errors];
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
