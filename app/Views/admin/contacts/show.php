<?php $statusLabels = ['new' => 'Nueva', 'contacted' => 'Contactada', 'closed' => 'Cerrada']; ?>
<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Solicitud #<?= (int) $contact['id'] ?></span><h1><?= e($contact['company']) ?></h1><p>Recibida el <?= e(date('d/m/Y \a \l\a\s H:i', strtotime($contact['created_at']))) ?></p></div>
    <a class="btn admin-secondary-btn" href="<?= url('/admin/contactos') ?>">Volver al listado</a>
</div>
<div class="row g-4">
    <div class="col-xl-8"><section class="admin-panel detail-panel"><div class="admin-panel__header"><div><span class="admin-eyebrow">Datos enviados</span><h2><?= e($contact['service']) ?></h2></div><span class="status-badge status-<?= e($contact['status']) ?>"><?= e($statusLabels[$contact['status']] ?? '') ?></span></div>
        <dl class="admin-details"><div><dt>Nombre</dt><dd><?= e($contact['name']) ?></dd></div><div><dt>Empresa</dt><dd><?= e($contact['company']) ?></dd></div><div><dt>Correo</dt><dd><a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a></dd></div><div><dt>Teléfono</dt><dd><?= e($contact['phone'] ?: 'No informado') ?></dd></div><div class="admin-details__wide"><dt>Mensaje</dt><dd><?= nl2br(e($contact['message'] ?: 'Sin mensaje adicional.')) ?></dd></div></dl>
    </section></div>
    <div class="col-xl-4"><section class="admin-panel admin-side-card"><h2>Gestión de la solicitud</h2>
        <?php if (\App\Core\Auth::can('contacts.manage')): ?><form method="post" action="<?= url('/admin/contactos/estado') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $contact['id'] ?>"><label class="form-label" for="contact-status">Estado</label><select class="form-select" id="contact-status" name="status"><?php foreach ($statusLabels as $value => $label): ?><option value="<?= e($value) ?>" <?= selected($contact['status'], $value) ?>><?= e($label) ?></option><?php endforeach; ?></select><button class="btn admin-primary-btn w-100 mt-3" type="submit">Actualizar estado</button></form><?php endif; ?>
        <?php if ($contact['reviewed_at']): ?><p class="admin-meta">Última revisión: <?= e(date('d/m/Y H:i', strtotime($contact['reviewed_at']))) ?><?= $contact['reviewer_name'] ? ' · ' . e($contact['reviewer_name']) : '' ?></p><?php endif; ?>
        <?php if (\App\Core\Auth::can('contacts.delete')): ?><form class="mt-4 pt-4 border-top" method="post" action="<?= url('/admin/contactos/eliminar') ?>" onsubmit="return confirm('¿Eliminar esta solicitud de forma permanente?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $contact['id'] ?>"><button class="btn admin-danger-btn w-100" type="submit">Eliminar solicitud</button></form><?php endif; ?>
    </section></div>
</div>
