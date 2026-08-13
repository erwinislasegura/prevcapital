<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Control de acceso</span><h1>Usuarios</h1><p>Administre credenciales, roles y estado de acceso.</p></div>
    <?php if (\App\Core\Auth::can('users.create')): ?><a class="btn admin-primary-btn" href="<?= url('/admin/users/create') ?>">Nuevo usuario</a><?php endif; ?>
</div>

<section class="admin-panel">
    <div class="admin-panel__header admin-panel__header--compact"><div><h2>Usuarios registrados</h2><p><?= count($users) ?> cuenta<?= count($users) === 1 ? '' : 's' ?> en el sistema</p></div></div>
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead><tr><th>Usuario</th><th>Roles</th><th>Último acceso</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><div class="table-user"><span><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span><div><strong><?= e($user['name']) ?></strong><small><?= e($user['email']) ?></small></div></div></td>
                    <td><span class="role-label"><?= e($user['role_names'] ?: 'Sin rol') ?></span></td>
                    <td><?= $user['last_login_at'] ? e(date('d/m/Y H:i', strtotime($user['last_login_at']))) : '<span class="text-muted">Sin ingreso</span>' ?></td>
                    <td><span class="status-badge <?= (int) $user['status'] === 1 ? 'active' : 'inactive' ?>"><?= (int) $user['status'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
                    <td><div class="table-actions justify-content-end">
                        <?php if (\App\Core\Auth::can('users.edit')): ?><a class="btn admin-icon-btn" href="<?= url('/admin/users/edit?id=' . (int) $user['id']) ?>" title="Editar" aria-label="Editar <?= e($user['name']) ?>"><svg viewBox="0 0 24 24"><path d="m4 16-1 5 5-1L19 9l-4-4L4 16Z"></path><path d="m13 7 4 4"></path></svg></a><?php endif; ?>
                        <?php if (\App\Core\Auth::can('users.edit') && (int) $user['id'] !== \App\Core\Auth::id()): ?><form method="post" action="<?= url('/admin/users/toggle') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button class="btn admin-icon-btn" type="submit" title="Cambiar estado" aria-label="Cambiar estado"><svg viewBox="0 0 24 24"><path d="M12 3v9"></path><path d="M7 5.5a8 8 0 1 0 10 0"></path></svg></button></form><?php endif; ?>
                        <?php if (\App\Core\Auth::can('users.delete') && (int) $user['id'] !== \App\Core\Auth::id()): ?><form method="post" action="<?= url('/admin/users/delete') ?>" onsubmit="return confirm('¿Eliminar este usuario de forma permanente?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $user['id'] ?>"><button class="btn admin-icon-btn admin-icon-btn--danger" type="submit" title="Eliminar" aria-label="Eliminar"><svg viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14M10 11v6M14 11v6"></path></svg></button></form><?php endif; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
