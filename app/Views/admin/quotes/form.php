<?php
$isEdit = $quote !== null;
$values = flash('old') ?: [];
$value = static fn (string $key, mixed $default = ''): mixed => $values[$key] ?? ($quote[$key] ?? $default);
$descriptions = $values['item_description'] ?? array_column($quote['items'] ?? [], 'description');
$details = $values['item_detail'] ?? array_column($quote['items'] ?? [], 'detail');
$quantities = $values['item_quantity'] ?? array_column($quote['items'] ?? [], 'quantity');
$prices = $values['item_unit_price'] ?? array_column($quote['items'] ?? [], 'unit_price');
if (!$descriptions) { $descriptions = ['']; $details = ['']; $quantities = [1]; $prices = [0]; }
?>
<div class="admin-page-heading"><div><span class="admin-eyebrow">Gestión comercial</span><h1><?= $isEdit ? 'Editar cotización' : 'Nueva cotización' ?></h1><p>Defina cliente, alcance, valores y vigencia de la propuesta.</p></div><a class="btn admin-secondary-btn" href="<?= $isEdit ? url('/admin/cotizaciones/ver?id=' . (int) $quote['id']) : url('/admin/cotizaciones') ?>">Cancelar</a></div>
<form class="admin-panel admin-form-panel" method="post" action="<?= $isEdit ? url('/admin/cotizaciones/editar?id=' . (int) $quote['id']) : url('/admin/cotizaciones/crear') ?>" id="quote-form">
    <?= csrf_field() ?>
    <div class="admin-form-section"><div><span>01</span><h2>Cliente</h2><p>Información que aparecerá en la propuesta y se utilizará para el envío.</p></div><div class="row g-4"><div class="col-md-6"><label class="form-label">Nombre de contacto</label><input class="form-control" name="client_name" maxlength="140" required value="<?= e($value('client_name')) ?>"></div><div class="col-md-6"><label class="form-label">Empresa</label><input class="form-control" name="company" maxlength="160" required value="<?= e($value('company')) ?>"></div><div class="col-md-6"><label class="form-label">Correo</label><input class="form-control" type="email" name="email" required value="<?= e($value('email')) ?>"></div><div class="col-md-3"><label class="form-label">Teléfono</label><input class="form-control" name="phone" value="<?= e($value('phone')) ?>"></div><div class="col-md-3"><label class="form-label">RUT</label><input class="form-control" name="tax_id" value="<?= e($value('tax_id')) ?>"></div><div class="col-12"><label class="form-label">Dirección</label><input class="form-control" name="address" maxlength="255" value="<?= e($value('address')) ?>"></div></div></div>
    <div class="admin-form-section"><div><span>02</span><h2>Propuesta</h2><p>Identificación y periodo de validez de la cotización.</p></div><div class="row g-4"><div class="col-12"><label class="form-label">Asunto</label><input class="form-control" name="subject" maxlength="200" required value="<?= e($value('subject')) ?>" placeholder="Ej. Implementación DS N°44"></div><div class="col-md-4"><label class="form-label">Fecha de emisión</label><input class="form-control" type="date" name="issue_date" required value="<?= e($value('issue_date', date('Y-m-d'))) ?>"></div><div class="col-md-4"><label class="form-label">Válida hasta</label><input class="form-control" type="date" name="valid_until" required value="<?= e($value('valid_until', date('Y-m-d', strtotime('+15 days')))) ?>"></div><div class="col-md-4"><label class="form-label">IVA (%)</label><input class="form-control quote-tax" type="number" min="0" max="100" step="0.01" name="tax_rate" required value="<?= e($value('tax_rate', 19)) ?>"></div></div></div>
    <div class="admin-form-section admin-form-section--items"><div><span>03</span><h2>Partidas</h2><p>Detalle los servicios, cantidades y valores. Los totales se recalculan en el servidor.</p></div><div><div id="quote-items" class="quote-items">
        <?php foreach ($descriptions as $index => $description): ?><article class="quote-item"><div class="quote-item__top"><strong>Partida <span><?= $index + 1 ?></span></strong><button class="btn quote-item__remove" type="button">Quitar</button></div><div class="row g-3"><div class="col-md-8"><label class="form-label">Descripción</label><input class="form-control" name="item_description[]" maxlength="220" required value="<?= e($description) ?>"></div><div class="col-md-2"><label class="form-label">Cantidad</label><input class="form-control quote-quantity" name="item_quantity[]" type="number" step="0.01" min="0.01" required value="<?= e($quantities[$index] ?? 1) ?>"></div><div class="col-md-2"><label class="form-label">Valor unitario</label><input class="form-control quote-price" name="item_unit_price[]" type="number" step="0.01" min="0" required value="<?= e($prices[$index] ?? 0) ?>"></div><div class="col-md-10"><label class="form-label">Detalle opcional</label><input class="form-control" name="item_detail[]" value="<?= e($details[$index] ?? '') ?>" placeholder="Alcance, entregables u observaciones de la partida"></div><div class="col-md-2 quote-item__total"><span>Total partida</span><strong>$0</strong></div></div></article><?php endforeach; ?>
    </div><button class="btn admin-secondary-btn mt-3" id="add-quote-item" type="button">Agregar partida</button><div class="quote-summary"><div><span>Subtotal</span><strong id="quote-subtotal">$0</strong></div><div><span>IVA</span><strong id="quote-tax">$0</strong></div><div class="quote-summary__total"><span>Total</span><strong id="quote-total">$0</strong></div></div></div></div>
    <div class="admin-form-section"><div><span>04</span><h2>Condiciones</h2><p>Información complementaria incluida en el PDF y en la cotización web.</p></div><div class="row g-4"><div class="col-12"><label class="form-label">Observaciones</label><textarea class="form-control" name="notes" rows="3"><?= e($value('notes')) ?></textarea></div><div class="col-12"><label class="form-label">Condiciones comerciales</label><textarea class="form-control" name="terms" rows="4"><?= e($value('terms', $defaultTerms)) ?></textarea></div><?php if (!$isEdit && \App\Core\Auth::can('quotes.send')): ?><div class="col-12"><div class="form-check form-switch admin-switch"><input class="form-check-input" id="send-now" type="checkbox" name="send_now" value="1"><label class="form-check-label" for="send-now">Enviar automáticamente al guardar (PDF adjunto y enlace de respuesta)</label></div></div><?php endif; ?></div></div>
    <div class="admin-form-actions"><a class="btn admin-secondary-btn" href="<?= url('/admin/cotizaciones') ?>">Cancelar</a><button class="btn admin-primary-btn" type="submit"><?= $isEdit ? 'Guardar cambios' : 'Crear cotización' ?></button></div>
</form>
<template id="quote-item-template"><article class="quote-item"><div class="quote-item__top"><strong>Partida <span></span></strong><button class="btn quote-item__remove" type="button">Quitar</button></div><div class="row g-3"><div class="col-md-8"><label class="form-label">Descripción</label><input class="form-control" name="item_description[]" maxlength="220" required></div><div class="col-md-2"><label class="form-label">Cantidad</label><input class="form-control quote-quantity" name="item_quantity[]" type="number" step="0.01" min="0.01" required value="1"></div><div class="col-md-2"><label class="form-label">Valor unitario</label><input class="form-control quote-price" name="item_unit_price[]" type="number" step="0.01" min="0" required value="0"></div><div class="col-md-10"><label class="form-label">Detalle opcional</label><input class="form-control" name="item_detail[]" placeholder="Alcance, entregables u observaciones de la partida"></div><div class="col-md-2 quote-item__total"><span>Total partida</span><strong>$0</strong></div></div></article></template>
<script>
(() => {
    const list = document.getElementById('quote-items');
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
        const rate = parseFloat(document.querySelector('.quote-tax').value) || 0;
        const tax = subtotal * rate / 100;
        document.getElementById('quote-subtotal').textContent = format(subtotal);
        document.getElementById('quote-tax').textContent = format(tax);
        document.getElementById('quote-total').textContent = format(subtotal + tax);
    };
    document.getElementById('add-quote-item').addEventListener('click', () => { list.append(document.getElementById('quote-item-template').content.cloneNode(true)); recalculate(); });
    list.addEventListener('click', event => { if (event.target.classList.contains('quote-item__remove') && list.children.length > 1) { event.target.closest('.quote-item').remove(); recalculate(); } });
    document.getElementById('quote-form').addEventListener('input', recalculate);
    recalculate();
})();
</script>
