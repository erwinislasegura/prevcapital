# PrevCapital — PHP MVC

Sitio corporativo y panel administrativo de PrevCapital, desarrollado en PHP MVC, MySQL y Bootstrap 5.3.8 incluido localmente.

## Requisitos

- PHP 8.1 o superior.
- MySQL 5.7/8.0 o MariaDB 10.4 o superior.
- Extensiones PHP `pdo_mysql`, `mbstring`, `iconv` y `fileinfo`.
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
- Clientes: `/admin/clientes`
- Cotizaciones: `/admin/cotizaciones`
- Email Marketing: `/admin/email-marketing`
- Configuración inicial: `/setup`

## Correo de cotizaciones y adjuntos

El módulo utiliza la función `mail()` de PHP, compatible con el servicio de correo habitual de cPanel. Para que los enlaces enviados sean absolutos, configure `APP_URL` con el dominio público (por ejemplo, `https://prevcapital.cl`). Los remitentes se pueden definir mediante:

- `MAIL_FROM_ADDRESS`: dirección remitente.
- `MAIL_FROM_NAME`: nombre visible del remitente.
- `MAIL_REPLY_TO`: dirección para respuestas.
- `MAIL_NOTIFICATION_ADDRESS`: correo que recibe avisos de aceptación o rechazo.

Si no se definen, se utiliza `contacto@prevcapital.cl`. Cada envío incluye el PDF corporativo y un enlace seguro de 64 caracteres a la cotización web.

Las cotizaciones también admiten hasta cinco adjuntos PDF, Word o imagen (JPG, PNG y WEBP), con un máximo de 8 MB por archivo y 15 MB en total. Los archivos se validan por extensión y contenido MIME, se guardan con nombre aleatorio en `storage/quote_attachments` y Apache bloquea su acceso directo. El servidor web debe tener permiso de escritura en esa carpeta.

Para mejorar la entrega, se recomienda utilizar SMTP autenticado en lugar de `mail()`:

- `MAIL_TRANSPORT=smtp`
- `MAIL_HOST`: servidor SMTP del dominio.
- `MAIL_PORT`: normalmente `587` con TLS o `465` con SSL.
- `MAIL_ENCRYPTION`: `tls`, `ssl` o `none`.
- `MAIL_USERNAME` y `MAIL_PASSWORD`: cuenta autenticada.
- `MAIL_TIMEOUT`: tiempo máximo de conexión, por defecto 20 segundos.
- `MAIL_DKIM_SELECTOR`: selector DKIM publicado por cPanel, normalmente `default`.

Las cotizaciones conservan compatibilidad con `mail()` si `MAIL_TRANSPORT` no se define. Email Marketing, en cambio, exige SMTP y registros SPF, DKIM y DMARC válidos antes de programar envíos.

## Email Marketing

El módulo `/admin/email-marketing` incorpora:

- Plantilla HTML corporativa editable y versión alternativa de texto.
- Marcadores `{{nombre}}`, `{{empresa}}`, `{{correo}}`, `{{contenido}}`, `{{contenido_texto}}` y `{{unsubscribe_url}}`.
- Importación manual, clientes activos y solicitudes web, con eliminación automática de duplicados.
- Consentimiento obligatorio, registro de origen y exclusión permanente de contactos desuscritos.
- Mensajes individuales con encabezados `List-Unsubscribe` y baja de un clic.
- Revisión en servidor de SMTP, SPF, DKIM y DMARC.
- Cola global con un único envío cada diez minutos y hasta tres intentos por destinatario.

Ninguna aplicación puede garantizar que un correo nunca llegue a spam: también influyen la reputación del dominio/IP, el contenido y la interacción del receptor. Por ello el sistema bloquea campañas sin autenticación de dominio, muestra advertencias de contenido y evita envíos masivos simultáneos.

### Cron de la cola

En cPanel, cree una tarea cron cada minuto. El proceso ejecuta como máximo un correo y la base de datos impide que el siguiente salga antes de diez minutos:

```bash
* * * * * /usr/local/bin/php /home/USUARIO/public_html/cron/email-marketing.php >/dev/null 2>&1
```

Ajuste la ruta de PHP y la carpeta según su cuenta. También existe el botón `Procesar siguiente envío` en el panel para pruebas manuales; respeta el mismo bloqueo de diez minutos.

Después de actualizar una instalación existente, ingrese una vez al panel o a `/admin/email-marketing` con un superadministrador para crear automáticamente las tablas y permisos antes de activar el cron.

## Contacto y redes del encabezado

Los datos visibles en la franja superior, página de contacto y pie de página se configuran con:

- `CONTACT_EMAIL`
- `CONTACT_PHONE_PRIMARY`
- `CONTACT_PHONE_SECONDARY`
- `CONTACT_LOCATION`
- `CONTACT_COVERAGE`

Los perfiles sociales se habilitan al definir sus URL completas:

- `SOCIAL_INSTAGRAM_URL`
- `SOCIAL_FACEBOOK_URL`
- `SOCIAL_LINKEDIN_URL`

Mientras una URL no esté configurada, el icono permanece visible en estado inactivo y no dirige a una cuenta no verificada.

## Módulos comerciales

- El formulario público guarda cada solicitud —incluida la dotación de trabajadores— en MySQL y permite gestionarla como nueva, contactada o cerrada.
- Las cotizaciones admiten partidas, descuento porcentual o fijo, cálculo de IVA sobre el neto, PDF A4 corporativo, envío inmediato o posterior, vista web y respuesta de aceptación/rechazo.
- La cartera de clientes permite guardar datos comerciales, reutilizarlos mediante un selector en cotizaciones y crear un cliente directamente desde una propuesta existente.
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
