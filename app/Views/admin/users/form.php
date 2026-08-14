<?php
$isEdit = $user !== null;
$oldValues = flash('old') ?: [];
$nameValue = $oldValues['name'] ?? ($user['name'] ?? '');
$emailValue = $oldValues['email'] ?? ($user['email'] ?? '');
$selectedRoles = isset($oldValues['role_ids']) ? array_map('intval', (array) $oldValues['role_ids']) : $selectedRoles;
$statusChecked = $oldValues ? isset($oldValues['status']) : (!$isEdit || (int) $user['status'] === 1);
?>
<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Control de acceso</span><h1><?= $isEdit ? 'Editar usuario' : 'Nuevo usuario' ?></h1><p><?= $isEdit ? 'Actualice sus datos, roles o estado.' : 'Cree una cuenta y defina sus permisos mediante roles.' ?></p></div>
    <a class="btn admin-secondary-btn" href="<?= url('/admin/users') ?>">Volver al listado</a>
</div>

<form class="admin-panel admin-form-panel" method="post" action="<?= $isEdit ? url('/admin/users/edit?id=' . (int) $user['id']) : url('/admin/users/create') ?>">
    <?= csrf_field() ?>
    <div class="admin-form-section"><div><span>01</span><h2>Información del usuario</h2><p>Datos que identifican la cuenta dentro del sistema.</p></div><div class="row g-4"><div class="col-md-6"><label class="form-label" for="name">Nombre completo</label><input class="form-control" id="name" name="name" value="<?= e($nameValue) ?>" required></div><div class="col-md-6"><label class="form-label" for="email">Correo electrónico</label><input class="form-control" id="email" name="email" type="email" value="<?= e($emailValue) ?>" required></div><div class="col-md-6"><label class="form-label" for="password"><?= $isEdit ? 'Nueva contraseña' : 'Contraseña' ?></label><input class="form-control" id="password" name="password" type="password" minlength="10" <?= $isEdit ? '' : 'required' ?>><div class="form-text"><?= $isEdit ? 'Déjela vacía para conservar la actual.' : 'Mínimo 10 caracteres.' ?></div></div><div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch admin-switch mb-2"><input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" <?= checked($statusChecked) ?>><label class="form-check-label" for="status">Usuario activo y autorizado para ingresar</label></div></div></div></div>
    <div class="admin-form-section"><div><span>02</span><h2>Roles asignados</h2><p>Los permisos efectivos se obtienen de los roles seleccionados.</p></div><div class="role-selector"><?php foreach ($roles as $role): ?><label><input type="checkbox" name="role_ids[]" value="<?= (int) $role['id'] ?>" <?= checked(in_array((int) $role['id'], $selectedRoles, true)) ?>><span><strong><?= e($role['name']) ?></strong><small><?= e($role['description'] ?: 'Rol personalizado') ?></small></span></label><?php endforeach; ?></div></div>
    <div class="admin-form-actions"><a class="btn admin-secondary-btn" href="<?= url('/admin/users') ?>">Cancelar</a><button class="btn admin-primary-btn" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg><?= $isEdit ? 'Guardar cambios' : 'Crear usuario' ?></button></div>
</form>
