# cdb-form

Plugin de formularios personalizados para [proyectocdb.es](https://proyectocdb.es). Proporciona herramientas para que empleados y bares puedan registrar su información y experiencia.

## Instalación

1. Copia la carpeta `cdb-form` en el directorio `wp-content/plugins` de tu instalación de WordPress.
2. Activa el plugin desde el panel de administración.
3. Al activarlo se crean las tablas personalizadas necesarias y se registran los Custom Post Types (CPT) usados por los formularios.

## Estructura de carpetas

- `cdb-form.php` – archivo principal del plugin.
- `includes/` – lógica del plugin (registro de CPT, bases de datos, shortcodes y procesadores AJAX).
- `templates/` – plantillas PHP que se cargan al usar los shortcodes.
- `admin/` y `public/` – carga de scripts/estilos para el panel de administración y el frontend.
- `assets/` – ficheros CSS y JavaScript utilizados por las interfaces.
- `docs/` – documentación adicional del proyecto.

## Uso de shortcodes

Inserta estos shortcodes en una página o entrada para mostrar los distintos formularios o listados:

- `[cdb_form_bar]` – formulario para crear o editar un bar asociado al usuario.
- `[cdb_form_empleado]` – formulario para crear o actualizar el perfil de empleado.
- `[cdb_experiencia]` – formulario de experiencia laboral con listado de entradas guardadas.
- `[cdb_bienvenida_usuario]` – mensaje de bienvenida que carga distintas secciones según el rol.
- `[cdb_top_empleados_experiencia_precalculada]` – ranking de empleados por puntuación de experiencia.
- `[cdb_top_empleados_puntuacion_total]` – ranking de empleados por puntuación gráfica.

## Procesamiento de formularios

Los formularios funcionan mediante llamadas AJAX a `admin-ajax.php` y se validan con nonces de seguridad:

1. Las plantillas (`templates/`) generan los formularios HTML y sus scripts asociados.
2. Al enviarse, se ejecutan las funciones en `includes/form-handler.php` o `includes/ajax-functions.php`, que comprueban permisos y guardan los datos.
3. La experiencia laboral se guarda en la tabla personalizada `wp_cdb_experiencia`, mientras que los perfiles de empleados y bares se almacenan como CPT.
4. Tras procesar la petición, el manejador devuelve una respuesta JSON que permite recargar la página o actualizar el contenido de forma dinámica.

Para más información consulta la carpeta `docs/`.

## Internacionalización

El plugin carga las traducciones desde la carpeta `languages` mediante `load_plugin_textdomain()` al iniciarse. Para generar el archivo `cdb-form.pot` se utilizan las funciones de internacionalización de WordPress en todo el código.
