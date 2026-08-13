<?php
$isEdit = $role !== null;
$oldValues = flash('old') ?: [];
$nameValue = $oldValues['name'] ?? ($role['name'] ?? '');
$descriptionValue = $oldValues['description'] ?? ($role['description'] ?? '');
$selectedPermissions = isset($oldValues['permission_ids']) ? array_map('intval', (array) $oldValues['permission_ids']) : $selectedPermissions;
?>
<div class="admin-page-heading"><div><span class="admin-eyebrow">Autorizaciones</span><h1><?= $isEdit ? 'Editar rol' : 'Nuevo rol' ?></h1><p>Configure el alcance del perfil por módulo y acción.</p></div><a class="btn admin-secondary-btn" href="<?= url('/admin/roles') ?>">Volver al listado</a></div>
<form class="admin-panel admin-form-panel" method="post" action="<?= $isEdit ? url('/admin/roles/edit?id=' . (int) $role['id']) : url('/admin/roles/create') ?>">
    <?= csrf_field() ?>
    <div class="admin-form-section"><div><span>01</span><h2>Identificación del rol</h2><p>Use un nombre claro que represente la responsabilidad del perfil.</p></div><div class="row g-4"><div class="col-md-5"><label class="form-label" for="name">Nombre del rol</label><input class="form-control" id="name" name="name" value="<?= e($nameValue) ?>" required <?= $isEdit && (int) $role['is_system'] === 1 ? '' : '' ?>></div><div class="col-md-7"><label class="form-label" for="description">Descripción</label><input class="form-control" id="description" name="description" value="<?= e($descriptionValue) ?>" placeholder="Responsabilidad y alcance del rol"></div></div></div>
    <div class="admin-form-section"><div><span>02</span><h2>Permisos del rol</h2><p>Seleccione únicamente las acciones necesarias para este perfil.</p></div><div class="permission-groups"><?php foreach ($permissions as $module => $items): ?><fieldset><legend><?= e($module) ?></legend><div><?php foreach ($items as $permission): ?><label><input type="checkbox" name="permission_ids[]" value="<?= (int) $permission['id'] ?>" <?= checked(in_array((int) $permission['id'], $selectedPermissions, true)) ?>><span><strong><?= e($permission['name']) ?></strong><small><?= e($permission['description']) ?></small></span></label><?php endforeach; ?></div></fieldset><?php endforeach; ?></div></div>
    <div class="admin-form-actions"><a class="btn admin-secondary-btn" href="<?= url('/admin/roles') ?>">Cancelar</a><button class="btn admin-primary-btn" type="submit"><?= $isEdit ? 'Guardar permisos' : 'Crear rol' ?></button></div>
</form>
