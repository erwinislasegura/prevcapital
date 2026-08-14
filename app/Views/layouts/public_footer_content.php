<?php
$footerContact = (array) \App\Core\Config::get('app.contact', []);
$footerEmail = (string) ($footerContact['email'] ?? 'contacto@prevcapital.cl');
$footerPhonePrimary = (string) ($footerContact['phone_primary'] ?? '+56 9 6418 0365');
$footerPhoneSecondary = (string) ($footerContact['phone_secondary'] ?? '+56 9 8597 4082');
$footerLocation = (string) ($footerContact['location'] ?? 'La Serena, Chile');
$footerCoverage = (string) ($footerContact['coverage'] ?? 'Región de Coquimbo, Chile');
$footerPhoneHref = static fn (string $phone): string => preg_replace('/[^+\d]/', '', $phone) ?: '';
?>
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
        <div class="footer__contact">
            <strong>Contacto</strong>
            <a href="mailto:<?= e($footerEmail) ?>"><?= e($footerEmail) ?></a>
            <div class="footer__phones"><a href="tel:<?= e($footerPhoneHref($footerPhonePrimary)) ?>"><?= e($footerPhonePrimary) ?></a><span>/</span><a href="tel:<?= e($footerPhoneHref($footerPhoneSecondary)) ?>"><?= e($footerPhoneSecondary) ?></a></div>
            <span><?= e($footerLocation) ?></span>
            <span>Cobertura <?= e($footerCoverage) ?></span>
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
        <span><?= e($footerLocation) ?> · Cobertura <?= e($footerCoverage) ?></span>
    </div>
</footer>
