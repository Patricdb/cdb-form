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

Los avisos, errores e instrucciones mostrados por estos shortcodes pueden personalizarse desde el submenú **Configuración de Mensajes y Avisos** dentro del menú "CdB Form" del panel de administración. El saludo mostrado por `[cdb_bienvenida_usuario]` es fijo y funciona como título de página.

### Configuración de mensajes y avisos

En esta pantalla es posible editar el contenido de cada mensaje y definir variantes de avisos con sus propias clases CSS. Además de los mensajes de bienvenida iniciales, es posible configurar avisos como:

- Acceso restringido para usuarios no autenticados.
- Empleado sin perfil o sin experiencia registrada.
- Listados de empleados o bares sin resultados.
- Puntuaciones o posiciones no disponibles.
- Mensajes de error por falta de permisos o recursos vinculados al usuario (por ejemplo, bar sin registrar).

Cada variante permite elegir un color de fondo y otro de texto. Si no se especifica color de texto, el sistema calcula automáticamente uno con contraste adecuado.

Para añadir una nueva variante introduce nombre, clase CSS, color de fondo y color de texto. Posteriormente podrás usar esa variante en los shortcodes seleccionándola en los mensajes correspondientes.

También puedes registrar variantes desde código utilizando `cdb_form_register_tipo_color( $slug, $args )`, pasando un array con `name`, `class`, `color` y `text`.

## Mensajes configurables

<!-- ACTUALIZAR ESTA TABLA SI SE AÑADEN NUEVAS CLAVES EN $cdb_form_defaults -->
| Clave | Texto por defecto | Contexto (dónde aparece) |
| --- | --- | --- |
| cdb_acceso_sin_login | **Debes iniciar sesión para acceder.** \| Inicia sesión o regístrate para continuar. | Formularios de bar o empleado cuando el usuario no ha iniciado sesión |
| cdb_acceso_sin_permisos | **No tienes permisos para ver este contenido.** \| Contacta con un admin si crees que es un error. | Formulario de empleado cuando el usuario no tiene permisos |
| cdb_aviso_sin_puntuacion | **Puntuación gráfica no disponible.** \| Añade más valoraciones para generar tu gráfico. | Panel de empleado cuando no hay gráfico |
| cdb_bares_sin_resultados | **No hay bares que coincidan con tu búsqueda.** \| Ajusta filtros o prueba con otro término. | Búsqueda de bares sin resultados |
| cdb_empleado_no_encontrado | **Empleado no encontrado.** \| Crea primero tu perfil de empleado para continuar. | Formulario de empleado cuando no existe perfil asociado |
| cdb_empleados_sin_resultados | **Sin coincidencias para tu búsqueda.** \| Modifica los criterios e inténtalo de nuevo. | Búsqueda de empleados sin resultados |
| cdb_empleados_vacio | **Aún no hay empleados registrados.** \| ¡Sé el primero en unirte al proyecto! | Rankings de empleados cuando no existen registros |
| cdb_experiencia_sin_perfil | **Para registrar experiencia debes crear tu perfil.** \| Completa tu información de empleado y vuelve aquí. | Formulario de experiencia sin perfil de empleado |
| cdb_ajax_exito_empleado | **Empleado creado correctamente.** \| El perfil se ha guardado sin problemas. | Respuesta exitosa al crear empleado por AJAX |
| cdb_ajax_error_empleado | **Error al crear empleado.** \| Inténtalo de nuevo más tarde. | Fallo al crear empleado por AJAX |
| cdb_ajax_exito_experiencia | **Experiencia registrada.** \| Se ha guardado la experiencia. | Registro de experiencia por AJAX |
| cdb_ajax_empleados_sin_resultados | **Sin resultados.** \| No hay empleados que coincidan con tu búsqueda. | Búsqueda de empleados sin coincidencias (AJAX) |
| cdb_ajax_bares_sin_resultados | **Sin resultados.** \| No hay bares que coincidan con tu búsqueda. | Búsqueda de bares sin coincidencias (AJAX) |
| cdb_ajax_disponibilidad_actualizada | **Disponibilidad actualizada correctamente.** \| Los datos se han guardado. | Actualización de disponibilidad del empleado |
| cdb_ajax_error_disponibilidad | **Hubo un problema al actualizar la disponibilidad.** \| Inténtalo de nuevo más tarde. | Error al actualizar disponibilidad del empleado |
| cdb_ajax_estado_bar_actualizado | **Estado del bar actualizado correctamente.** \| Los datos se han guardado. | Actualización del estado del bar |
| cdb_ajax_error_estado_bar | **Hubo un problema al actualizar el estado del bar.** \| Inténtalo de nuevo más tarde. | Error al actualizar estado del bar |
| cdb_ajax_error_comunicacion | **Error de comunicación.** \| No se pudo contactar con el servidor. | Fallo de conexión AJAX |
| cdb_ajax_error_anio_cifras | **El año debe tener 4 cifras.** \| Introduce un año válido. | Validación de filtros de búsqueda (año con 4 cifras) |
| cdb_ajax_error_nombre_invalido | **Selecciona un nombre válido.** \| Elige una opción de la lista. | Validación de filtro de nombre en búsqueda de empleados |
| cdb_ajax_error_posicion_invalida | **Selecciona una posición válida.** \| Usa la ayuda de autocompletado. | Validación de filtro de posición en búsqueda de empleados |
| cdb_ajax_error_bar_invalido | **Selecciona un bar válido.** \| Usa la ayuda de autocompletado. | Validación de filtro de bar en búsquedas |
| cdb_ajax_error_anio_invalido | **Selecciona un año válido.** \| Usa un formato de cuatro cifras. | Validación de filtro de año en búsqueda de empleados |
| cdb_ajax_error_zona_invalida | **Selecciona una zona válida.** \| Elige una opción de la lista. | Validación de filtro de zona en búsqueda de bares |

Para personalizar estos textos ve al submenú **Cdb Form → Configuración de Mensajes y Avisos**.

## Procesamiento de formularios

Los formularios funcionan mediante llamadas AJAX a `admin-ajax.php` y se validan con nonces de seguridad:

1. Las plantillas (`templates/`) generan los formularios HTML y sus scripts asociados.
2. Al enviarse, se ejecutan las funciones en `includes/form-handler.php` o `includes/ajax-functions.php`, que comprueban permisos y guardan los datos.
3. La experiencia laboral se guarda en la tabla personalizada `wp_cdb_experiencia`, mientras que los perfiles de empleados y bares se almacenan como CPT.
4. Tras procesar la petición, el manejador devuelve una respuesta JSON que permite recargar la página o actualizar el contenido de forma dinámica.

Para más información consulta la carpeta `docs/`.

## Internacionalización

El plugin carga las traducciones desde la carpeta `languages` mediante `load_plugin_textdomain()` al iniciarse. Para generar el archivo `cdb-form.pot` se utilizan las funciones de internacionalización de WordPress en todo el código.

## Traducciones

Para crear archivos de traducción personalizados sigue estos pasos:

1. Genera el archivo `.pot` con la utilidad de WP-CLI:

   ```bash
   wp i18n make-pot . languages/cdb-form.pot
   ```

2. Crea un archivo `.po` para tu idioma a partir del `.pot` (ejemplo para español de España):

   ```bash
   msginit --locale=es_ES --input=languages/cdb-form.pot --output-file=languages/es_ES.po
   ```

3. Rellena los `msgstr` del `.po` y compílalo a `.mo`:

   ```bash
   msgfmt languages/es_ES.po -o languages/es_ES.mo
   ```

Coloca los archivos generados en la carpeta `languages` para que WordPress los cargue automáticamente.

## Actualización del POT

Para regenerar el archivo `cdb-form.pot` ejecuta:

```bash
npm install && npm run pot
```
