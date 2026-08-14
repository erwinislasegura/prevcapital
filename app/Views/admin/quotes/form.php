<?php
$isEdit = $quote !== null;
$values = flash('old') ?: [];
$clientDefaults = $preselectedClient ? [
    'client_name' => $preselectedClient['name'],
    'company' => $preselectedClient['company'],
    'email' => $preselectedClient['email'],
    'phone' => $preselectedClient['phone'],
    'tax_id' => $preselectedClient['tax_id'],
    'address' => $preselectedClient['address'],
] : [];
$value = static fn (string $key, mixed $default = ''): mixed => $values[$key] ?? ($quote[$key] ?? ($clientDefaults[$key] ?? $default));
$selectedClientId = (int) ($values['client_id'] ?? ($quote['client_id'] ?? ($preselectedClient['id'] ?? 0)));
$descriptions = $values['item_description'] ?? array_column($quote['items'] ?? [], 'description');
$details = $values['item_detail'] ?? array_column($quote['items'] ?? [], 'detail');
$quantities = $values['item_quantity'] ?? array_column($quote['items'] ?? [], 'quantity');
$prices = $values['item_unit_price'] ?? array_column($quote['items'] ?? [], 'unit_price');
if (!$descriptions) { $descriptions = ['']; $details = ['']; $quantities = [1]; $prices = [0]; }
?>
<div class="admin-page-heading"><div><span class="admin-eyebrow">Gestión comercial</span><h1><?= $isEdit ? 'Editar cotización' : 'Nueva cotización' ?></h1><p>Defina cliente, alcance, valores y vigencia de la propuesta.</p></div><a class="btn admin-secondary-btn" href="<?= $isEdit ? url('/admin/cotizaciones/ver?id=' . (int) $quote['id']) : url('/admin/cotizaciones') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"></path></svg>Volver</a></div>
<form class="admin-panel admin-form-panel" method="post" action="<?= $isEdit ? url('/admin/cotizaciones/editar?id=' . (int) $quote['id']) : url('/admin/cotizaciones/crear') ?>" id="quote-form">
    <?= csrf_field() ?>
    <div class="admin-form-section"><div><span>01</span><h2>Cliente</h2><p>Seleccione un cliente guardado o complete los datos para esta propuesta.</p></div><div class="row g-3"><div class="col-lg-9"><label class="form-label" for="client-selector">Cliente guardado</label><select class="form-select" id="client-selector" name="client_id"><option value="">Completar datos manualmente</option><?php foreach ($clients as $savedClient): ?><option value="<?= (int) $savedClient['id'] ?>" <?= selected($selectedClientId, $savedClient['id']) ?> data-name="<?= e($savedClient['name']) ?>" data-company="<?= e($savedClient['company']) ?>" data-email="<?= e($savedClient['email']) ?>" data-phone="<?= e($savedClient['phone']) ?>" data-tax-id="<?= e($savedClient['tax_id']) ?>" data-address="<?= e($savedClient['address']) ?>"><?= e($savedClient['company'] . ' · ' . $savedClient['name']) ?></option><?php endforeach; ?></select><div class="form-text">Al seleccionar un cliente se completarán sus datos automáticamente.</div></div><?php if (\App\Core\Auth::can('clients.create')): ?><div class="col-lg-3 d-flex align-items-end"><a class="btn admin-secondary-btn admin-outline-teal-btn w-100" target="_blank" rel="noopener" href="<?= url('/admin/clientes/crear') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>Nuevo cliente</a></div><?php endif; ?><div class="col-md-6"><label class="form-label" for="client-name">Nombre de contacto</label><input class="form-control" id="client-name" name="client_name" maxlength="140" required value="<?= e($value('client_name')) ?>"></div><div class="col-md-6"><label class="form-label" for="client-company">Empresa</label><input class="form-control" id="client-company" name="company" maxlength="160" required value="<?= e($value('company')) ?>"></div><div class="col-md-6"><label class="form-label" for="client-email">Correo</label><input class="form-control" id="client-email" type="email" name="email" required value="<?= e($value('email')) ?>"></div><div class="col-md-3"><label class="form-label" for="client-phone">Teléfono</label><input class="form-control" id="client-phone" name="phone" value="<?= e($value('phone')) ?>"></div><div class="col-md-3"><label class="form-label" for="client-tax-id">RUT</label><input class="form-control" id="client-tax-id" name="tax_id" value="<?= e($value('tax_id')) ?>"></div><div class="col-12"><label class="form-label" for="client-address">Dirección</label><input class="form-control" id="client-address" name="address" maxlength="255" value="<?= e($value('address')) ?>"></div><?php if (\App\Core\Auth::can('clients.create')): ?><div class="col-12"><div class="form-check form-switch admin-switch quote-save-client"><input class="form-check-input" id="save-client" type="checkbox" name="save_client" value="1" <?= checked(isset($values['save_client'])) ?>><label class="form-check-label" for="save-client">Guardar estos datos como cliente para futuras cotizaciones</label></div></div><?php endif; ?></div></div>
    <div class="admin-form-section"><div><span>02</span><h2>Propuesta</h2><p>Identificación, vigencia, descuento e impuestos de la cotización.</p></div><div class="row g-3"><div class="col-12"><label class="form-label">Asunto</label><input class="form-control" name="subject" maxlength="200" required value="<?= e($value('subject')) ?>" placeholder="Ej. Implementación DS N°44"></div><div class="col-md-3"><label class="form-label">Fecha de emisión</label><input class="form-control" type="date" name="issue_date" required value="<?= e($value('issue_date', date('Y-m-d'))) ?>"></div><div class="col-md-3"><label class="form-label">Válida hasta</label><input class="form-control" type="date" name="valid_until" required value="<?= e($value('valid_until', date('Y-m-d', strtotime('+15 days')))) ?>"></div><div class="col-md-2"><label class="form-label" for="discount-type">Tipo descuento</label><select class="form-select quote-discount-type" id="discount-type" name="discount_type"><option value="percentage" <?= selected($value('discount_type', 'percentage'), 'percentage') ?>>Porcentaje</option><option value="fixed" <?= selected($value('discount_type'), 'fixed') ?>>Monto fijo</option></select></div><div class="col-md-2"><label class="form-label" for="discount-value">Descuento</label><div class="input-group"><span class="input-group-text" id="discount-prefix">%</span><input class="form-control quote-discount-value" id="discount-value" type="number" min="0" step="0.01" name="discount_value" value="<?= e($value('discount_value', 0)) ?>" aria-describedby="discount-prefix"></div></div><div class="col-md-2"><label class="form-label">IVA (%)</label><input class="form-control quote-tax" type="number" min="0" max="100" step="0.01" name="tax_rate" required value="<?= e($value('tax_rate', 19)) ?>"></div></div></div>
    <div class="admin-form-section admin-form-section--items"><div><span>03</span><h2>Partidas</h2><p>Detalle los servicios, cantidades y valores. Los totales se recalculan en el servidor.</p></div><div><div id="quote-items" class="quote-items">
        <?php foreach ($descriptions as $index => $description): ?><article class="quote-item"><div class="quote-item__top"><strong>Partida <span><?= $index + 1 ?></span></strong><button class="btn quote-item__remove" type="button">Quitar</button></div><div class="row g-3"><div class="col-md-8"><label class="form-label">Descripción</label><input class="form-control" name="item_description[]" maxlength="220" required value="<?= e($description) ?>"></div><div class="col-md-2"><label class="form-label">Cantidad</label><input class="form-control quote-quantity" name="item_quantity[]" type="number" step="0.01" min="0.01" required value="<?= e($quantities[$index] ?? 1) ?>"></div><div class="col-md-2"><label class="form-label">Valor unitario</label><input class="form-control quote-price" name="item_unit_price[]" type="number" step="0.01" min="0" required value="<?= e($prices[$index] ?? 0) ?>"></div><div class="col-md-10"><label class="form-label">Detalle opcional</label><input class="form-control" name="item_detail[]" value="<?= e($details[$index] ?? '') ?>" placeholder="Alcance, entregables u observaciones de la partida"></div><div class="col-md-2 quote-item__total"><span>Total partida</span><strong>$0</strong></div></div></article><?php endforeach; ?>
    </div><button class="btn admin-secondary-btn admin-outline-teal-btn mt-3" id="add-quote-item" type="button"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>Agregar partida</button><div class="quote-summary"><div><span>Subtotal</span><strong id="quote-subtotal">$0</strong></div><div><span id="quote-discount-label">Descuento</span><strong id="quote-discount">-$0</strong></div><div><span>IVA</span><strong id="quote-tax">$0</strong></div><div class="quote-summary__total"><span>Total</span><strong id="quote-total">$0</strong></div></div></div></div>
    <div class="admin-form-section"><div><span>04</span><h2>Condiciones</h2><p>Información complementaria incluida en el PDF y en la cotización web.</p></div><div class="row g-4"><div class="col-12"><label class="form-label">Observaciones</label><textarea class="form-control" name="notes" rows="3"><?= e($value('notes')) ?></textarea></div><div class="col-12"><label class="form-label">Condiciones comerciales</label><textarea class="form-control" name="terms" rows="4"><?= e($value('terms', $defaultTerms)) ?></textarea></div><?php if (!$isEdit && \App\Core\Auth::can('quotes.send')): ?><div class="col-12"><div class="form-check form-switch admin-switch"><input class="form-check-input" id="send-now" type="checkbox" name="send_now" value="1"><label class="form-check-label" for="send-now">Enviar automáticamente al guardar (PDF adjunto y enlace de respuesta)</label></div></div><?php endif; ?></div></div>
    <div class="admin-form-actions"><a class="btn admin-secondary-btn" href="<?= url('/admin/cotizaciones') ?>">Cancelar</a><button class="btn admin-primary-btn" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg><?= $isEdit ? 'Guardar cambios' : 'Crear cotización' ?></button></div>
</form>
<template id="quote-item-template"><article class="quote-item"><div class="quote-item__top"><strong>Partida <span></span></strong><button class="btn quote-item__remove" type="button">Quitar</button></div><div class="row g-3"><div class="col-md-8"><label class="form-label">Descripción</label><input class="form-control" name="item_description[]" maxlength="220" required></div><div class="col-md-2"><label class="form-label">Cantidad</label><input class="form-control quote-quantity" name="item_quantity[]" type="number" step="0.01" min="0.01" required value="1"></div><div class="col-md-2"><label class="form-label">Valor unitario</label><input class="form-control quote-price" name="item_unit_price[]" type="number" step="0.01" min="0" required value="0"></div><div class="col-md-10"><label class="form-label">Detalle opcional</label><input class="form-control" name="item_detail[]" placeholder="Alcance, entregables u observaciones de la partida"></div><div class="col-md-2 quote-item__total"><span>Total partida</span><strong>$0</strong></div></div></article></template>
<script>
(() => {
    const list = document.getElementById('quote-items');
    const clientSelector = document.getElementById('client-selector');
    const saveClient = document.getElementById('save-client');
    const discountType = document.querySelector('.quote-discount-type');
    const discountValue = document.querySelector('.quote-discount-value');
    const syncClientState = fill => {
        const option = clientSelector.options[clientSelector.selectedIndex];
        if (fill && option.value) {
            document.getElementById('client-name').value = option.dataset.name || '';
            document.getElementById('client-company').value = option.dataset.company || '';
            document.getElementById('client-email').value = option.dataset.email || '';
            document.getElementById('client-phone').value = option.dataset.phone || '';
            document.getElementById('client-tax-id').value = option.dataset.taxId || '';
            document.getElementById('client-address').value = option.dataset.address || '';
        }
        if (saveClient) {
            saveClient.disabled = Boolean(option.value);
            if (option.value) saveClient.checked = false;
            saveClient.closest('.quote-save-client').classList.toggle('is-disabled', Boolean(option.value));
        }
    };
    const format = value => new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP', maximumFractionDigits:0}).format(value || 0);
    const recalculate = () => {
        let subtotal = 0;
        [...list.querySelectorAll('.quote-item')].forEach((item, index) => {
            item.querySelector('.quote-item__top span').textContent = index + 1;
            const quantity = parseFloat(item.querySelector('.quote-quantity').value) || 0;
            const price = parseFloat(item.querySelector('.quote-price').value) || 0;
            const total = quantity * price;
            subtotal += total;
            item.querySelector('.quote-item__total strong').textContent = format(total);
            item.querySelector('.quote-item__remove').disabled = list.children.length === 1;
        });
        const discountInput = Math.max(parseFloat(discountValue.value) || 0, 0);
        const discount = Math.min(discountType.value === 'percentage' ? subtotal * discountInput / 100 : discountInput, subtotal);
        const taxable = Math.max(subtotal - discount, 0);
        const rate = parseFloat(document.querySelector('.quote-tax').value) || 0;
        const tax = taxable * rate / 100;
        document.getElementById('discount-prefix').textContent = discountType.value === 'percentage' ? '%' : '$';
        discountValue.max = discountType.value === 'percentage' ? '100' : String(Math.max(subtotal, 0));
        document.getElementById('quote-subtotal').textContent = format(subtotal);
        document.getElementById('quote-discount-label').textContent = discountType.value === 'percentage' ? `Descuento (${discountInput.toLocaleString('es-CL')}%)` : 'Descuento fijo';
        document.getElementById('quote-discount').textContent = '-' + format(discount);
        document.getElementById('quote-tax').textContent = format(tax);
        document.getElementById('quote-total').textContent = format(taxable + tax);
    };
    document.getElementById('add-quote-item').addEventListener('click', () => { list.append(document.getElementById('quote-item-template').content.cloneNode(true)); recalculate(); });
    clientSelector.addEventListener('change', () => syncClientState(true));
    list.addEventListener('click', event => { if (event.target.classList.contains('quote-item__remove') && list.children.length > 1) { event.target.closest('.quote-item').remove(); recalculate(); } });
    document.getElementById('quote-form').addEventListener('input', recalculate);
    syncClientState(false);
    recalculate();
})();
</script>
