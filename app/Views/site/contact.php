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
                <article><span>01</span><div><strong>Respuesta orientada a empresas</strong><p>Analizamos su necesidad desde la realidad de la operación.</p></div></article>
                <article><span>02</span><div><strong>Alcance definido con claridad</strong><p>Servicios, entregables, etapas y prioridades identificadas.</p></div></article>
                <article><span>03</span><div><strong>Atención en Chile</strong><p>Proyectos preventivos para organizaciones de distintos sectores.</p></div></article>
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
                <label class="form-honeypot" aria-hidden="true">Sitio web<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                <label>Nombre y apellido<input type="text" placeholder="Ej. Carolina Muñoz" required maxlength="140" name="nombre" value="<?= e(old('nombre')) ?>"></label>
                <label>Empresa<input type="text" placeholder="Nombre de la organización" required maxlength="160" name="empresa" value="<?= e(old('empresa')) ?>"></label>
                <div><label>Correo corporativo<input type="email" placeholder="nombre@empresa.cl" required maxlength="180" name="correo" value="<?= e(old('correo')) ?>"></label><label>Teléfono<input type="tel" placeholder="+56 9..." maxlength="60" name="telefono" value="<?= e(old('telefono')) ?>"></label></div>
                <label>¿Qué necesita resolver?<select name="servicio" required><option value="" disabled <?= old('servicio') ? '' : 'selected' ?>>Seleccione un servicio</option><?php foreach (['Diagnóstico preventivo','Implementación DS N°44','Protocolos MINSAL','Carpeta de arranque minería','ISO 45001','Capacitaciones','Otro requerimiento'] as $service): ?><option <?= selected(old('servicio'), $service) ?>><?= e($service) ?></option><?php endforeach; ?></select></label>
                <label>Cuéntenos brevemente<textarea name="mensaje" placeholder="Describa la necesidad de su empresa" rows="4" maxlength="3000"><?= e(old('mensaje')) ?></textarea></label>
                <button class="button button--primary button--full" type="submit">Solicitar contacto <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></button>
            </form>
            <small>Atención a empresas y organizaciones en Chile</small>
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
