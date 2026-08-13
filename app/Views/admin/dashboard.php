<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Resumen operativo</span><h1>Panel de control</h1><p>Usuarios, accesos y actividad reciente del sistema.</p></div>
    <?php if (\App\Core\Auth::can('users.create')): ?><a class="btn admin-primary-btn" href="<?= url('/admin/users/create') ?>">Agregar usuario</a><?php endif; ?>
</div>

<div class="row g-4 admin-metrics">
    <div class="col-md-4"><article><div class="metric-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 19c.4-4 2.4-6 6-6s5.6 2 6 6M16 6c1.8.3 2.8 1.2 2.8 2.8S18 11.3 16 12M17 14c2 .7 3.2 2.3 3.5 4.5"></path></svg></div><div><span>Usuarios registrados</span><strong><?= (int) $metrics['users'] ?></strong><small>Accesos creados</small></div></article></div>
    <div class="col-md-4"><article><div class="metric-icon metric-icon--teal"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></div><div><span>Usuarios activos</span><strong><?= (int) $metrics['activeUsers'] ?></strong><small>Con acceso habilitado</small></div></article></div>
    <div class="col-md-4"><article><div class="metric-icon"><svg viewBox="0 0 24 24"><path d="M12 3 5 6v5c0 4.8 2.8 8.2 7 10 4.2-1.8 7-5.2 7-10V6l-7-3Z"></path></svg></div><div><span>Roles configurados</span><strong><?= (int) $metrics['roles'] ?></strong><small>Perfiles de permisos</small></div></article></div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-7">
        <section class="admin-panel h-100">
            <div class="admin-panel__header"><div><span class="admin-eyebrow">Accesos</span><h2>Usuarios recientes</h2></div><?php if (\App\Core\Auth::can('users.view')): ?><a href="<?= url('/admin/users') ?>">Ver todos</a><?php endif; ?></div>
            <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>Usuario</th><th>Estado</th><th>Registro</th></tr></thead><tbody>
            <?php foreach ($recentUsers as $user): ?><tr><td><div class="table-user"><span><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span><div><strong><?= e($user['name']) ?></strong><small><?= e($user['email']) ?></small></div></div></td><td><span class="status-badge <?= (int) $user['status'] === 1 ? 'active' : 'inactive' ?>"><?= (int) $user['status'] === 1 ? 'Activo' : 'Inactivo' ?></span></td><td><?= e(date('d/m/Y', strtotime($user['created_at']))) ?></td></tr><?php endforeach; ?>
            <?php if (!$recentUsers): ?><tr><td colspan="3" class="empty-cell">Aún no existen usuarios adicionales.</td></tr><?php endif; ?>
            </tbody></table></div>
        </section>
    </div>
    <div class="col-xl-5">
        <section class="admin-panel h-100">
            <div class="admin-panel__header"><div><span class="admin-eyebrow">Trazabilidad</span><h2>Actividad reciente</h2></div></div>
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): ?><article><span class="activity-dot"></span><div><strong><?= e(str_replace(['.', '_'], [' · ', ' '], $activity['action'])) ?></strong><p><?= e($activity['details'] ?: 'Acción registrada en el sistema.') ?></p><small><?= e($activity['user_name'] ?: 'Sistema') ?> · <?= e(date('d/m/Y H:i', strtotime($activity['created_at']))) ?></small></div></article><?php endforeach; ?>
                <?php if (!$recentActivity): ?><p class="empty-cell">Aún no hay actividad registrada.</p><?php endif; ?>
            </div>
        </section>
    </div>
</div>
