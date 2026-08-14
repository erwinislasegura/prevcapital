<footer>
    <div class="container footer__main">
        <div class="footer__brand">
            <img src="<?= asset('assets/images/logo-prevcapital.png') ?>" alt="PrevCapital">
            <p>Seguridad y Salud en el Trabajo<br>con visión estratégica.</p>
        </div>
        <div>
            <strong>Navegación</strong>
            <a href="<?= url('/') ?>">Inicio</a>
            <a href="<?= url('/servicios') ?>">Servicios</a>
            <a href="<?= url('/cumplimiento') ?>">Cumplimiento</a>
            <a href="<?= url('/nosotros') ?>">Nosotros</a>
            <a href="<?= url('/contacto') ?>">Contacto</a>
        </div>
        <div>
            <strong>Especialidades</strong>
            <span>DS N°44</span>
            <span>Protocolos MINSAL</span>
            <span>ISO 45001</span>
            <span>Carpetas de arranque</span>
        </div>
        <div>
            <strong>Gestión</strong>
            <a class="footer__admin" href="<?= url('/admin') ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11V8a5 5 0 0 1 10 0v3"></path><path d="M5 11h14v10H5z"></path></svg>
                Panel de administración
            </a>
        </div>
    </div>
    <div class="container footer__bottom">
        <span>© 2026 PrevCapital. Todos los derechos reservados.</span>
        <span>Consultoría en prevención de riesgos · Chile</span>
    </div>
</footer>
