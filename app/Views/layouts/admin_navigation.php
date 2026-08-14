<div class="admin-brand">
    <img src="<?= asset('assets/images/logo-prevcapital.png') ?>" alt="PrevCapital">
</div>
<nav class="admin-nav" aria-label="Navegación administrativa">
    <span class="admin-nav__caption">Administración</span>
    <?php if (\App\Core\Auth::can('dashboard.view')): ?>
    <a class="<?= $isDashboard ? 'active' : '' ?>" href="<?= url('/admin') ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"></path></svg><span>Panel general</span>
    </a>
    <?php endif; ?>
    <?php if (\App\Core\Auth::can('contacts.view')): ?>
    <a class="<?= $isContacts ? 'active' : '' ?>" href="<?= url('/admin/contactos') ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v12H8l-4 3V5Z"></path><path d="M8 9h8M8 13h5"></path></svg><span>Contactos</span>
    </a>
    <?php endif; ?>
    <?php if (\App\Core\Auth::can('quotes.view')): ?>
    <a class="<?= $isQuotes ? 'active' : '' ?>" href="<?= url('/admin/cotizaciones') ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l3 3v15H6V3Z"></path><path d="M14 3v4h4M9 11h6M9 15h6"></path></svg><span>Cotizaciones</span>
    </a>
    <?php endif; ?>
    <?php if (\App\Core\Auth::can('users.view')): ?>
    <a class="<?= $isUsers ? 'active' : '' ?>" href="<?= url('/admin/users') ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3"></circle><path d="M3 19c.4-4 2.4-6 6-6s5.6 2 6 6M16 5c2 .2 3 1.2 3 3s-1 2.8-3 3M17 13c2.3.6 3.6 2.4 4 5"></path></svg><span>Usuarios</span>
    </a>
    <?php endif; ?>
    <?php if (\App\Core\Auth::can('roles.view')): ?>
    <a class="<?= $isRoles ? 'active' : '' ?>" href="<?= url('/admin/roles') ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 5 6v5c0 4.8 2.8 8.2 7 10 4.2-1.8 7-5.2 7-10V6l-7-3Z"></path><path d="m9 12 2 2 4-5"></path></svg><span>Roles y permisos</span>
    </a>
    <?php endif; ?>
</nav>
<div class="admin-sidebar__footer">
    <a href="<?= url('/') ?>" target="_blank" rel="noopener"><span>Ver sitio público</span><svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"></path></svg></a>
    <form method="post" action="<?= url('/logout') ?>"><?= csrf_field() ?><button type="submit"><span>Cerrar sesión</span><svg viewBox="0 0 24 24"><path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"></path></svg></button></form>
</div>
