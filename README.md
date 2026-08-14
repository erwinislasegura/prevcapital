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
- Servicios: `/servicios`
- Cumplimiento normativo: `/cumplimiento`
- Nosotros y metodología: `/nosotros`
- Contacto: `/contacto`
- Inicio de sesión: `/login`
- Panel administrativo: `/admin`
- Solicitudes de contacto: `/admin/contactos`
- Cotizaciones: `/admin/cotizaciones`
- Configuración inicial: `/setup`

## Correo de cotizaciones

El módulo utiliza la función `mail()` de PHP, compatible con el servicio de correo habitual de cPanel. Para que los enlaces enviados sean absolutos, configure `APP_URL` con el dominio público (por ejemplo, `https://prevcapital.cl`). Los remitentes se pueden definir mediante:

- `MAIL_FROM_ADDRESS`: dirección remitente.
- `MAIL_FROM_NAME`: nombre visible del remitente.
- `MAIL_REPLY_TO`: dirección para respuestas.
- `MAIL_NOTIFICATION_ADDRESS`: correo que recibe avisos de aceptación o rechazo.

Si no se definen, se utiliza `contacto@prevcapital.cl`. Cada envío incluye el PDF corporativo y un enlace seguro de 64 caracteres a la cotización web.

## Módulos comerciales

- El formulario público guarda cada solicitud en MySQL y permite gestionarla como nueva, contactada o cerrada.
- Las cotizaciones admiten partidas, cálculo de IVA y total, PDF A4 corporativo, envío inmediato o posterior, vista web y respuesta de aceptación/rechazo.
- Las acciones relevantes se incorporan a la trazabilidad del panel.

## Estructura

- `app/Controllers`: controladores HTTP.
- `app/Models`: acceso a MySQL.
- `app/Views`: vistas públicas, administrativas y layouts.
- `assets`: CSS e imágenes optimizadas.
- `config`: configuración de aplicación y base de datos.
- `core`: router, autenticación, vistas, CSRF y conexión PDO.
- `database/schema.sql`: esquema, roles y permisos iniciales.
- `routes/web.php`: rutas públicas y protegidas.

El sitio público conserva la identidad visual de PrevCapital. Bootstrap se utiliza únicamente en el panel administrativo y formularios funcionales, sin modificar la interfaz pública.

El encabezado, pie de página, navegación, estilos y JavaScript público están centralizados. Las cinco rutas públicas reutilizan los mismos layouts y mantienen estados activos de navegación en escritorio y dispositivos móviles.
