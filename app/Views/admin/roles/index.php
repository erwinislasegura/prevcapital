<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Autorizaciones</span><h1>Roles y permisos</h1><p>Defina con precisión qué puede consultar y administrar cada perfil.</p></div>
    <?php if (\App\Core\Auth::can('roles.create')): ?><a class="btn admin-primary-btn" href="<?= url('/admin/roles/create') ?>">Nuevo rol</a><?php endif; ?>
</div>
<div class="row g-4">
<?php foreach ($roles as $role): ?>
    <div class="col-md-6 col-xl-4"><article class="role-card h-100"><div class="role-card__top"><span class="role-card__icon"><svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.8 2.8 8.2 7 10 4.2-1.8 7-5.2 7-10V6l-7-3Z"></path><path d="m9 12 2 2 4-5"></path></svg></span><?php if ((int) $role['is_system'] === 1): ?><small>Protegido</small><?php else: ?><small>Personalizado</small><?php endif; ?></div><h2><?= e($role['name']) ?></h2><p><?= e($role['description'] ?: 'Perfil de permisos personalizado para el equipo.') ?></p><div class="role-card__stats"><span><strong><?= (int) $role['user_count'] ?></strong> usuarios</span><span><strong><?= (int) $role['permission_count'] ?></strong> permisos</span></div><div class="role-card__actions"><?php if (\App\Core\Auth::can('roles.edit')): ?><a class="btn admin-secondary-btn" href="<?= url('/admin/roles/edit?id=' . (int) $role['id']) ?>">Configurar</a><?php endif; ?><?php if (\App\Core\Auth::can('roles.delete') && (int) $role['is_system'] !== 1): ?><form method="post" action="<?= url('/admin/roles/delete') ?>" onsubmit="return confirm('¿Eliminar este rol?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $role['id'] ?>"><button class="btn admin-danger-btn" type="submit">Eliminar</button></form><?php endif; ?></div></article></div>
<?php endforeach; ?>
</div>
