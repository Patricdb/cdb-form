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
- `[cdb_bienvenida_usuario]` – muestra siempre un saludo y un mensaje de bienvenida para cualquier usuario autenticado y, según su rol, carga el panel de empleado o empleador. Incluye mensajes específicos para empleados sin perfil o sin experiencia.
- `[cdb_top_empleados_experiencia_precalculada]` – ranking de empleados por puntuación de experiencia.
- `[cdb_top_empleados_puntuacion_total]` – ranking de empleados por puntuación gráfica.

Los mensajes y sus colores de fondo y texto mostrados por estos shortcodes pueden personalizarse desde el submenú **Configuración de Mensajes y Avisos** dentro del menú "CdB Form" del panel de administración. El saludo mostrado por `[cdb_bienvenida_usuario]` es fijo y funciona como título de página.

### Configuración de mensajes y avisos

En esta pantalla es posible editar el contenido de cada mensaje y definir variantes de avisos con sus propias clases CSS. Actualmente pueden configurarse el **Mensaje de Bienvenida**, el **Mensaje para Empleado sin perfil** y el **Mensaje para Empleado sin experiencia**. Cada variante permite elegir un color de fondo y otro de texto. Si no se especifica color de texto, el sistema calcula automáticamente uno con contraste adecuado.

Para añadir una nueva variante introduce nombre, clase CSS, color de fondo y color de texto. Posteriormente podrás usar esa variante en los shortcodes seleccionándola en los mensajes correspondientes.

También puedes registrar variantes desde código utilizando `cdb_form_register_tipo_color( $slug, $args )`, pasando un array con `name`, `class`, `color` y `text`.

## Procesamiento de formularios

Los formularios funcionan mediante llamadas AJAX a `admin-ajax.php` y se validan con nonces de seguridad:

1. Las plantillas (`templates/`) generan los formularios HTML y sus scripts asociados.
2. Al enviarse, se ejecutan las funciones en `includes/form-handler.php` o `includes/ajax-functions.php`, que comprueban permisos y guardan los datos.
3. La experiencia laboral se guarda en la tabla personalizada `wp_cdb_experiencia`, mientras que los perfiles de empleados y bares se almacenan como CPT.
4. Tras procesar la petición, el manejador devuelve una respuesta JSON que permite recargar la página o actualizar el contenido de forma dinámica.

Para más información consulta la carpeta `docs/`.

## Internacionalización

El plugin carga las traducciones desde la carpeta `languages` mediante `load_plugin_textdomain()` al iniciarse. Para generar el archivo `cdb-form.pot` se utilizan las funciones de internacionalización de WordPress en todo el código.
