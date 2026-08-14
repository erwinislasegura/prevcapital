<?php
$contactData = (array) \App\Core\Config::get('app.contact', []);
$contactEmail = (string) ($contactData['email'] ?? 'contacto@prevcapital.cl');
$primaryPhone = (string) ($contactData['phone_primary'] ?? '+56 9 6418 0365');
$secondaryPhone = (string) ($contactData['phone_secondary'] ?? '+56 9 8597 4082');
$contactLocation = (string) ($contactData['location'] ?? 'La Serena, Chile');
$contactCoverage = (string) ($contactData['coverage'] ?? 'Región de Coquimbo, Chile');
$phoneHref = static fn (string $phone): string => preg_replace('/[^+\d]/', '', $phone) ?: '';
?>
<section class="page-hero page-hero--contact">
    <div class="page-hero__overlay"></div>
    <div class="container page-hero__content">
        <nav class="breadcrumbs" aria-label="Migas de pan"><a href="<?= url('/') ?>">Inicio</a><span>/</span><span>Contacto</span></nav>
        <p class="eyebrow"><span>Evaluación inicial</span> Atención a empresas</p>
        <h1>Conversemos sobre las necesidades de su organización.</h1>
        <p>Cuéntenos qué necesita resolver. Prepararemos una orientación inicial para definir el servicio, el alcance y los próximos pasos.</p>
    </div>
</section>

<section class="contact contact--page" id="contacto">
    <div class="container contact__grid">
        <div class="contact__intro">
            <p class="section-kicker">Contacto PrevCapital</p>
            <h2>Identifique hoy las brechas que mañana pueden detener su operación.</h2>
            <p>Una conversación inicial nos permite comprender el contexto, revisar la urgencia y orientar la solución más adecuada.</p>
            <div class="contact-points">
                <article><span>01</span><div><strong>Correo</strong><p><a href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a></p></div></article>
                <article><span>02</span><div><strong>Teléfonos</strong><p><a href="tel:<?= e($phoneHref($primaryPhone)) ?>"><?= e($primaryPhone) ?></a> · <a href="tel:<?= e($phoneHref($secondaryPhone)) ?>"><?= e($secondaryPhone) ?></a></p></div></article>
                <article><span>03</span><div><strong><?= e($contactLocation) ?></strong><p>Cobertura <?= e($contactCoverage) ?></p></div></article>
            </div>
        </div>
        <div class="contact__panel">
            <p>Evaluación inicial para empresas</p>
            <h3>Solicite una reunión con PrevCapital</h3>
            <?php if ($message = flash('success')): ?><div class="form-notice form-notice--success" role="status"><?= e($message) ?></div><?php endif; ?>
            <?php if ($message = flash('error')): ?><div class="form-notice form-notice--error" role="alert"><?= e($message) ?></div><?php endif; ?>
            <?php if ($errors = flash('errors')): ?><div class="form-notice form-notice--error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <form action="<?= url('/contacto') ?>" method="post">
                <?= csrf_field() ?>
                <label>Nombre y apellido<input type="text" placeholder="Ej. Carolina Muñoz" required maxlength="140" name="nombre" autocomplete="name" value="<?= e(old('nombre')) ?>"></label>
                <label>Empresa<input type="text" placeholder="Nombre de la organización" required maxlength="160" name="empresa" autocomplete="organization" value="<?= e(old('empresa')) ?>"></label>
                <div><label>Correo corporativo<input type="email" placeholder="nombre@empresa.cl" required maxlength="180" name="correo" autocomplete="email" inputmode="email" value="<?= e(old('correo')) ?>"></label><label><span>Teléfono <small>(opcional)</small></span><input type="tel" placeholder="+56 9..." maxlength="60" name="telefono" autocomplete="tel" inputmode="tel" value="<?= e(old('telefono')) ?>"></label></div>
                <div class="contact-form__need"><label>N.º de trabajadores<input type="number" min="1" max="1000000" step="1" inputmode="numeric" placeholder="Ej. 25" required name="numero_trabajadores" value="<?= e(old('numero_trabajadores')) ?>"></label><label>¿Qué necesita resolver?<select name="servicio" required><option value="" disabled <?= old('servicio') ? '' : 'selected' ?>>Seleccione un servicio</option><?php foreach (['Diagnóstico preventivo','Implementación DS N°44','Protocolos MINSAL','Carpeta de arranque minería','ISO 45001','Capacitaciones','Otro requerimiento'] as $service): ?><option <?= selected(old('servicio'), $service) ?>><?= e($service) ?></option><?php endforeach; ?></select></label></div>
                <label><span>Cuéntenos brevemente <small>(opcional)</small></span><textarea name="mensaje" placeholder="Describa la necesidad de su empresa" rows="4" maxlength="3000"><?= e(old('mensaje')) ?></textarea></label>
                <button class="button button--primary button--full" type="submit">Solicitar contacto <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></button>
            </form>
            <small>Atención en <?= e($contactLocation) ?> · Cobertura <?= e($contactCoverage) ?></small>
        </div>
    </div>
</section>

<section class="contact-next section">
    <div class="container">
        <div class="section-heading"><div><p class="section-kicker">Qué ocurre después</p><h2>Un inicio simple y ordenado.</h2></div><p>El primer contacto busca comprender su necesidad y definir si corresponde una reunión, diagnóstico o propuesta técnica.</p></div>
        <div class="contact-next__grid">
            <article><span>01</span><h3>Recibimos su solicitud</h3><p>Revisamos el servicio, contexto y datos principales de la empresa.</p></article>
            <article><span>02</span><h3>Aclaramos el alcance</h3><p>Conversamos sobre operación, urgencia, riesgos y resultados esperados.</p></article>
            <article><span>03</span><h3>Definimos el siguiente paso</h3><p>Coordinamos una reunión o preparamos una propuesta según corresponda.</p></article>
        </div>
    </div>
</section>
