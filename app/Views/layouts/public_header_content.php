<div class="topbar">
    <div class="container topbar__inner">
        <span>Consultoría estratégica en Seguridad y Salud en el Trabajo</span>
        <span class="topbar__tag"><i></i> Soluciones para empresas</span>
    </div>
</div>
<header class="site-header">
    <div class="container header__inner">
        <a class="brand" href="<?= url('/') ?>" aria-label="PrevCapital, volver al inicio">
            <img src="<?= asset('assets/images/logo-prevcapital.png') ?>" alt="PrevCapital">
        </a>
        <nav class="desktop-nav" id="public-navigation" aria-label="Navegación principal" data-site-nav>
            <a class="<?= ($activePage ?? '') === 'inicio' ? 'active' : '' ?>" href="<?= url('/') ?>">Inicio</a>
            <a class="<?= ($activePage ?? '') === 'servicios' ? 'active' : '' ?>" href="<?= url('/servicios') ?>">Servicios</a>
            <a class="<?= ($activePage ?? '') === 'cumplimiento' ? 'active' : '' ?>" href="<?= url('/cumplimiento') ?>">Cumplimiento</a>
            <a class="<?= ($activePage ?? '') === 'nosotros' ? 'active' : '' ?>" href="<?= url('/nosotros') ?>">Nosotros</a>
            <a class="<?= ($activePage ?? '') === 'contacto' ? 'active' : '' ?>" href="<?= url('/contacto') ?>">Contacto</a>
        </nav>
        <a class="button button--header header__cta" href="<?= url('/contacto') ?>">Solicitar diagnóstico <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="public-navigation" aria-label="Abrir menú" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
