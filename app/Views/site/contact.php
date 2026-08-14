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
            <form action="mailto:contacto@prevcapital.cl" enctype="text/plain" method="post">
                <label>Nombre y apellido<input type="text" placeholder="Ej. Carolina Muñoz" required name="nombre"></label>
                <label>Empresa<input type="text" placeholder="Nombre de la organización" required name="empresa"></label>
                <div><label>Correo corporativo<input type="email" placeholder="nombre@empresa.cl" required name="correo"></label><label>Teléfono<input type="tel" placeholder="+56 9..." name="telefono"></label></div>
                <label>¿Qué necesita resolver?<select name="servicio" required><option value="" disabled selected>Seleccione un servicio</option><option>Diagnóstico preventivo</option><option>Implementación DS N°44</option><option>Protocolos MINSAL</option><option>Carpeta de arranque minería</option><option>ISO 45001</option><option>Capacitaciones</option><option>Otro requerimiento</option></select></label>
                <label>Cuéntenos brevemente<textarea name="mensaje" placeholder="Describa la necesidad de su empresa" rows="4"></textarea></label>
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
