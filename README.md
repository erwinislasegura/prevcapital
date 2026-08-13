# PrevCapital — PHP MVC

Sitio corporativo y panel administrativo de PrevCapital, desarrollado en PHP MVC, MySQL y Bootstrap 5.3.8 incluido localmente.

## Requisitos

- PHP 8.1 o superior.
- MySQL 5.7/8.0 o MariaDB 10.4 o superior.
- Extensiones PHP `pdo_mysql`, `mbstring` e `iconv`.
- Apache con `mod_rewrite` habilitado.

## Instalación

1. Cree una base de datos MySQL vacía.
2. Copie `config/database.example.php` como `config/database.local.php` y configure allí la conexión. También puede usar las variables `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.
3. Suba el proyecto completo a la carpeta pública del dominio o subdominio.
4. Abra `/setup` en el navegador.
5. Cree el primer superadministrador. El sistema generará las tablas, roles y permisos automáticamente.
6. Después de crear el primer usuario, `/setup` queda deshabilitado y redirige al acceso administrativo.

Si el proyecto se instala en una subcarpeta, las rutas y recursos la detectan automáticamente. También puede definir `APP_URL` con la URL completa.

## Accesos

- Sitio público: `/`
- Inicio de sesión: `/login`
- Panel administrativo: `/admin`
- Configuración inicial: `/setup`

## Estructura

- `app/Controllers`: controladores HTTP.
- `app/Models`: acceso a MySQL.
- `app/Views`: vistas públicas, administrativas y layouts.
- `assets`: CSS e imágenes optimizadas.
- `config`: configuración de aplicación y base de datos.
- `core`: router, autenticación, vistas, CSRF y conexión PDO.
- `database/schema.sql`: esquema, roles y permisos iniciales.
- `routes/web.php`: rutas públicas y protegidas.

El sitio público conserva el diseño original de PrevCapital. Bootstrap se utiliza en el panel administrativo y formularios funcionales para mantener la portada sin alteraciones visuales.
