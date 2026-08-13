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

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r CROSS JOIN permissions p WHERE r.slug = 'superadmin';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('dashboard.view','users.view','users.create','users.edit','roles.view')
WHERE r.slug = 'administrador';

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.slug IN ('dashboard.view','users.view','roles.view')
WHERE r.slug = 'supervisor';
