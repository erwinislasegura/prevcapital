CREATE TABLE IF NOT EXISTS roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    is_system TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    module VARCHAR(60) NOT NULL,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(80) NULL,
    entity_id INT UNSIGNED NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created_at (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(140) NOT NULL,
    company VARCHAR(160) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(60) NULL,
    worker_count INT UNSIGNED NULL,
    service VARCHAR(140) NOT NULL,
    message TEXT NULL,
    status ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
    source VARCHAR(80) NOT NULL DEFAULT 'Formulario web',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    reviewed_by INT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_contact_status (status),
    INDEX idx_contact_created_at (created_at),
    CONSTRAINT fk_contact_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(140) NOT NULL,
    company VARCHAR(160) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(60) NULL,
    tax_id VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    notes TEXT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clients_company (company),
    INDEX idx_clients_email (email),
    INDEX idx_clients_status (status),
    CONSTRAINT fk_clients_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_clients_updater FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_number VARCHAR(40) NOT NULL UNIQUE,
    public_token CHAR(64) NOT NULL UNIQUE,
    client_id BIGINT UNSIGNED NULL,
    client_name VARCHAR(140) NOT NULL,
    company VARCHAR(160) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(60) NULL,
    tax_id VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    subject VARCHAR(200) NOT NULL,
    issue_date DATE NOT NULL,
    valid_until DATE NOT NULL,
    status ENUM('draft','sent','accepted','rejected') NOT NULL DEFAULT 'draft',
    currency CHAR(3) NOT NULL DEFAULT 'CLP',
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_type ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,2) NOT NULL DEFAULT 19,
    tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    terms TEXT NULL,
    sent_at DATETIME NULL,
    accepted_at DATETIME NULL,
    rejected_at DATETIME NULL,
    rejection_reason TEXT NULL,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_quotes_status (status),
    INDEX idx_quotes_created_at (created_at),
    INDEX idx_quotes_client_id (client_id),
    CONSTRAINT fk_quotes_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    CONSTRAINT fk_quotes_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_quotes_updater FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quote_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(220) NOT NULL,
    detail TEXT NULL,
    quantity DECIMAL(12,2) NOT NULL DEFAULT 1,
    unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_quote_items_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quote_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(60) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_quote_events_created_at (created_at),
    CONSTRAINT fk_quote_events_quote FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (name, slug, description, is_system) VALUES
('Superadministrador', 'superadmin', 'Acceso completo a todos los módulos y configuraciones.', 1),
('Administrador', 'administrador', 'Gestión operativa de usuarios y consulta de roles.', 1),
('Supervisor', 'supervisor', 'Acceso de lectura al panel administrativo.', 1);

INSERT IGNORE INTO permissions (name, slug, module, description) VALUES
('Ver panel', 'dashboard.view', 'Panel', 'Acceder al resumen administrativo.'),
('Ver usuarios', 'users.view', 'Usuarios', 'Consultar usuarios y sus roles.'),
('Crear usuarios', 'users.create', 'Usuarios', 'Registrar nuevos usuarios.'),
('Editar usuarios', 'users.edit', 'Usuarios', 'Modificar datos, roles y estado.'),
('Eliminar usuarios', 'users.delete', 'Usuarios', 'Eliminar usuarios del sistema.'),
('Ver roles', 'roles.view', 'Roles y permisos', 'Consultar roles y permisos.'),
('Crear roles', 'roles.create', 'Roles y permisos', 'Crear nuevos roles.'),
('Editar roles', 'roles.edit', 'Roles y permisos', 'Modificar roles y permisos.'),
('Eliminar roles', 'roles.delete', 'Roles y permisos', 'Eliminar roles no protegidos.');

INSERT IGNORE INTO permissions (name, slug, module, description) VALUES
('Ver contactos', 'contacts.view', 'Contactos', 'Consultar solicitudes recibidas desde el sitio web.'),
('Gestionar contactos', 'contacts.manage', 'Contactos', 'Actualizar el estado de las solicitudes.'),
('Eliminar contactos', 'contacts.delete', 'Contactos', 'Eliminar solicitudes de contacto.'),
('Ver cotizaciones', 'quotes.view', 'Cotizaciones', 'Consultar cotizaciones y su estado.'),
('Crear cotizaciones', 'quotes.create', 'Cotizaciones', 'Crear nuevas cotizaciones.'),
('Editar cotizaciones', 'quotes.edit', 'Cotizaciones', 'Modificar cotizaciones existentes.'),
('Enviar cotizaciones', 'quotes.send', 'Cotizaciones', 'Enviar cotizaciones por correo electrónico.'),
('Eliminar cotizaciones', 'quotes.delete', 'Cotizaciones', 'Eliminar cotizaciones del sistema.');

INSERT IGNORE INTO permissions (name, slug, module, description) VALUES
('Ver clientes', 'clients.view', 'Clientes', 'Consultar la cartera de clientes.'),
('Crear clientes', 'clients.create', 'Clientes', 'Registrar clientes para reutilizarlos en cotizaciones.'),
('Editar clientes', 'clients.edit', 'Clientes', 'Actualizar datos comerciales de clientes.'),
('Eliminar clientes', 'clients.delete', 'Clientes', 'Eliminar clientes sin borrar sus cotizaciones históricas.');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.slug = 'superadmin';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('dashboard.view','users.view','users.create','users.edit','roles.view','contacts.view','contacts.manage','contacts.delete','clients.view','clients.create','clients.edit','clients.delete','quotes.view','quotes.create','quotes.edit','quotes.send','quotes.delete')
WHERE r.slug = 'administrador';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('dashboard.view','users.view','roles.view','contacts.view','clients.view','quotes.view')
WHERE r.slug = 'supervisor';
