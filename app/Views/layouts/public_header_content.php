<?php
$contact = (array) \App\Core\Config::get('app.contact', []);
$contactEmail = (string) ($contact['email'] ?? 'contacto@prevcapital.cl');
$socialNetworks = (array) \App\Core\Config::get('app.social', []);
?>
<div class="topbar">
    <div class="container topbar__inner">
        <span class="topbar__message">Consultoría estratégica en Seguridad y Salud en el Trabajo</span>
        <div class="topbar__contact">
            <a class="topbar__email" href="mailto:<?= e($contactEmail) ?>" aria-label="Enviar correo a <?= e($contactEmail) ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m4 7 8 6 8-6"></path></svg>
                <span><?= e($contactEmail) ?></span>
            </a>
            <span class="topbar__divider" aria-hidden="true"></span>
            <nav class="topbar__social" aria-label="Redes sociales de PrevCapital">
                <?php foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn'] as $network => $label): ?>
                    <?php $socialUrl = trim((string) ($socialNetworks[$network] ?? '')); ?>
                    <?php if ($socialUrl !== ''): ?><a href="<?= e($socialUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="PrevCapital en <?= e($label) ?>"><?php else: ?><span aria-label="<?= e($label) ?>, enlace pendiente de configuración" title="<?= e($label) ?>"><?php endif; ?>
                        <?php if ($network === 'instagram'): ?><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.5" cy="6.5" r=".7"></circle></svg><?php endif; ?>
                        <?php if ($network === 'facebook'): ?><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 21v-8h3l.5-3H14V8.2c0-.9.3-1.7 1.8-1.7H18V3.8c-.4-.1-1.6-.2-2.8-.2-2.8 0-4.7 1.7-4.7 4.8V10H8v3h2.5v8"></path></svg><?php endif; ?>
                        <?php if ($network === 'linkedin'): ?><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="9" width="3" height="11"></rect><path d="M5.5 4.5a1.6 1.6 0 1 0 0 3.2 1.6 1.6 0 0 0 0-3.2ZM11 20V9h3v1.6c.8-1.2 2-2 3.7-2 2.7 0 3.3 1.8 3.3 4.6V20h-3v-6.1c0-1.5-.3-2.6-1.9-2.6-1.7 0-2.1 1.3-2.1 3V20h-3Z"></path></svg><?php endif; ?>
                    <?php if ($socialUrl !== ''): ?></a><?php else: ?></span><?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</div>
<header class="site-header">
    <div class="container header__inner">
        <a class="brand" href="<?= url('/') ?>" aria-label="PrevCapital, volver al inicio">
            <img src="<?= asset('assets/images/logo-prevcapital.png') ?>" alt="PrevCapital">
        </a>
        <nav class="desktop-nav" id="public-navigation" aria-label="Navegación principal" data-site-nav>
            <a class="<?= ($activePage ?? '') === 'inicio' ? 'active' : '' ?>" <?= ($activePage ?? '') === 'inicio' ? 'aria-current="page"' : '' ?> href="<?= url('/') ?>">Inicio</a>
            <a class="<?= ($activePage ?? '') === 'servicios' ? 'active' : '' ?>" <?= ($activePage ?? '') === 'servicios' ? 'aria-current="page"' : '' ?> href="<?= url('/servicios') ?>">Servicios</a>
            <a class="<?= ($activePage ?? '') === 'cumplimiento' ? 'active' : '' ?>" <?= ($activePage ?? '') === 'cumplimiento' ? 'aria-current="page"' : '' ?> href="<?= url('/cumplimiento') ?>">Cumplimiento</a>
            <a class="<?= ($activePage ?? '') === 'nosotros' ? 'active' : '' ?>" <?= ($activePage ?? '') === 'nosotros' ? 'aria-current="page"' : '' ?> href="<?= url('/nosotros') ?>">Nosotros</a>
            <a class="<?= ($activePage ?? '') === 'contacto' ? 'active' : '' ?>" <?= ($activePage ?? '') === 'contacto' ? 'aria-current="page"' : '' ?> href="<?= url('/contacto') ?>">Contacto</a>
        </nav>
        <a class="button button--header header__cta" href="<?= url('/contacto') ?>">Solicitar diagnóstico <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="public-navigation" aria-label="Abrir menú" data-nav-toggle>
            <span></span><span></span><span></span>
        </button>
    </div>
</header>
