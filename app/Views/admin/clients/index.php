<div class="admin-page-heading">
    <div><span class="admin-eyebrow">Gestión comercial</span><h1>Clientes</h1><p>Datos reutilizables para crear cotizaciones con mayor rapidez.</p></div>
    <?php if (\App\Core\Auth::can('clients.create')): ?><a class="btn admin-primary-btn" href="<?= url('/admin/clientes/crear') ?>">Nuevo cliente</a><?php endif; ?>
</div>
<section class="admin-panel">
    <form class="admin-filters" method="get" action="<?= url('/admin/clientes') ?>">
        <input class="form-control" name="search" value="<?= e($search) ?>" placeholder="Buscar empresa, contacto, RUT, correo o teléfono">
        <select class="form-select" name="status"><option value="">Todos los estados</option><option value="active" <?= selected($status, 'active') ?>>Activos</option><option value="inactive" <?= selected($status, 'inactive') ?>>Inactivos</option></select>
        <button class="btn admin-secondary-btn" type="submit">Filtrar</button>
    </form>
    <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>Cliente</th><th>Contacto</th><th>RUT / Teléfono</th><th>Cotizaciones</th><th>Total cotizado</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead><tbody>
    <?php foreach ($clients as $client): ?><tr>
        <td><strong><?= e($client['company']) ?></strong><small class="d-block text-muted"><?= e($client['address'] ?: 'Sin dirección registrada') ?></small></td>
        <td><div class="table-user"><span><?= e(mb_strtoupper(mb_substr($client['name'], 0, 1))) ?></span><div><strong><?= e($client['name']) ?></strong><small><?= e($client['email']) ?></small></div></div></td>
        <td><?= e($client['tax_id'] ?: 'Sin RUT') ?><small class="d-block text-muted"><?= e($client['phone'] ?: 'Sin teléfono') ?></small></td>
        <td><strong><?= (int) $client['quote_count'] ?></strong></td><td><strong><?= money_clp($client['quoted_total']) ?></strong></td>
        <td><span class="status-badge <?= (int) $client['status'] === 1 ? 'active' : 'inactive' ?>"><?= (int) $client['status'] === 1 ? 'Activo' : 'Inactivo' ?></span></td>
        <td><div class="table-actions justify-content-end">
            <?php if (\App\Core\Auth::can('quotes.create') && (int) $client['status'] === 1): ?><a class="btn admin-icon-btn" href="<?= url('/admin/cotizaciones/crear?client_id=' . (int) $client['id']) ?>" title="Crear cotización" aria-label="Crear cotización"><svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6V3Z"></path><path d="M12 10v7M8.5 13.5h7"></path></svg></a><?php endif; ?>
            <?php if (\App\Core\Auth::can('clients.edit')): ?><a class="btn admin-icon-btn" href="<?= url('/admin/clientes/editar?id=' . (int) $client['id']) ?>" title="Editar cliente" aria-label="Editar cliente"><svg viewBox="0 0 24 24"><path d="m4 16-1 5 5-1L19 9l-4-4L4 16Z"></path><path d="m13 7 4 4"></path></svg></a><?php endif; ?>
            <?php if (\App\Core\Auth::can('clients.delete')): ?><form method="post" action="<?= url('/admin/clientes/eliminar') ?>" onsubmit="return confirm('¿Eliminar este cliente? Las cotizaciones históricas se conservarán.')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $client['id'] ?>"><button class="btn admin-icon-btn admin-icon-btn--danger" type="submit" title="Eliminar cliente" aria-label="Eliminar cliente"><svg viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3M7 7l1 14h8l1-14"></path></svg></button></form><?php endif; ?>
        </div></td>
    </tr><?php endforeach; ?>
    <?php if (!$clients): ?><tr><td class="empty-cell" colspan="7">No hay clientes que coincidan con los filtros.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
