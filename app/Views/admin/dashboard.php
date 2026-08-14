<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Resumen operativo</span><h1>Panel de control</h1><p>Solicitudes, propuestas comerciales y actividad reciente.</p></div>
    <?php if (\App\Core\Auth::can('quotes.create')): ?><a class="btn admin-primary-btn" href="<?= url('/admin/cotizaciones/crear') ?>">Nueva cotización</a><?php endif; ?>
</div>

<div class="row g-4 admin-metrics">
    <div class="col-md-6 col-xl-3"><article><div class="metric-icon metric-icon--teal"><svg viewBox="0 0 24 24"><path d="M4 5h16v12H8l-4 3V5Z"></path></svg></div><div><span>Contactos nuevos</span><strong><?= (int) $metrics['newContacts'] ?></strong><small>Pendientes de gestión</small></div></article></div>
    <div class="col-md-6 col-xl-3"><article><div class="metric-icon"><svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6V3Z"></path></svg></div><div><span>Cotizaciones</span><strong><?= (int) $metrics['quotes'] ?></strong><small>Propuestas creadas</small></div></article></div>
    <div class="col-md-6 col-xl-3"><article><div class="metric-icon metric-icon--teal"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path></svg></div><div><span>Aceptadas</span><strong><?= (int) $metrics['acceptedQuotes'] ?></strong><small>Confirmadas por clientes</small></div></article></div>
    <div class="col-md-6 col-xl-3"><article><div class="metric-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3 19c.4-4 2.4-6 6-6s5.6 2 6 6"></path></svg></div><div><span>Usuarios activos</span><strong><?= (int) $metrics['activeUsers'] ?></strong><small>Con acceso habilitado</small></div></article></div>
</div>

<div class="row g-4 mt-1">
    <div class="col-xl-7">
        <section class="admin-panel h-100">
            <div class="admin-panel__header"><div><span class="admin-eyebrow">Atención comercial</span><h2>Contactos recientes</h2></div><?php if (\App\Core\Auth::can('contacts.view')): ?><a href="<?= url('/admin/contactos') ?>">Ver todos</a><?php endif; ?></div>
            <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>Contacto</th><th>Necesidad</th><th>Registro</th></tr></thead><tbody>
            <?php foreach ($recentContacts as $contact): ?><tr><td><div class="table-user"><span><?= e(mb_strtoupper(mb_substr($contact['name'], 0, 1))) ?></span><div><strong><?= e($contact['name']) ?></strong><small><?= e($contact['company']) ?></small></div></div></td><td><?= e($contact['service']) ?></td><td><?= e(date('d/m/Y', strtotime($contact['created_at']))) ?></td></tr><?php endforeach; ?>
            <?php if (!$recentContacts): ?><tr><td colspan="3" class="empty-cell">Aún no existen solicitudes de contacto.</td></tr><?php endif; ?>
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
