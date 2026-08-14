<?php $statusLabels = ['new' => 'Nueva', 'contacted' => 'Contactada', 'closed' => 'Cerrada']; ?>
<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Atención comercial</span><h1>Solicitudes de contacto</h1><p>Registros recibidos desde el formulario del sitio público.</p></div>
</div>
<section class="admin-panel">
    <form class="admin-filters" method="get" action="<?= url('/admin/contactos') ?>">
        <input class="form-control" name="search" value="<?= e($search) ?>" placeholder="Buscar nombre, empresa, correo o servicio">
        <select class="form-select" name="status"><option value="">Todos los estados</option><?php foreach ($statusLabels as $value => $label): ?><option value="<?= e($value) ?>" <?= selected($status, $value) ?>><?= e($label) ?></option><?php endforeach; ?></select>
        <button class="btn admin-secondary-btn" type="submit">Filtrar</button>
    </form>
    <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>Contacto</th><th>Empresa</th><th>Necesidad</th><th>Fecha</th><th>Estado</th><th class="text-end">Acción</th></tr></thead><tbody>
    <?php foreach ($contacts as $contact): ?><tr>
        <td><div class="table-user"><span><?= e(mb_strtoupper(mb_substr($contact['name'], 0, 1))) ?></span><div><strong><?= e($contact['name']) ?></strong><small><?= e($contact['email']) ?></small></div></div></td>
        <td><strong><?= e($contact['company']) ?></strong></td><td><?= e($contact['service']) ?></td><td><?= e(date('d/m/Y H:i', strtotime($contact['created_at']))) ?></td>
        <td><span class="status-badge status-<?= e($contact['status']) ?>"><?= e($statusLabels[$contact['status']] ?? $contact['status']) ?></span></td>
        <td class="text-end"><a class="btn admin-icon-btn ms-auto" href="<?= url('/admin/contactos/ver?id=' . (int) $contact['id']) ?>" aria-label="Ver solicitud" title="Ver solicitud"><svg viewBox="0 0 24 24"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"></path><circle cx="12" cy="12" r="2.5"></circle></svg></a></td>
    </tr><?php endforeach; ?>
    <?php if (!$contacts): ?><tr><td class="empty-cell" colspan="6">No hay solicitudes que coincidan con los filtros.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
