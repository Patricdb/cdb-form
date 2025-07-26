# cdb-form

Plugin de formularios personalizados para [proyectocdb.es](https://proyectocdb.es).

## Instalación

1. Copia la carpeta `cdb-form` en el directorio `wp-content/plugins` de tu instalación de WordPress.
2. Activa el plugin desde el panel de administración.
3. Al activarlo se crearán las tablas necesarias y se registrarán los CPT utilizados.

## Uso básico

El plugin proporciona varios *shortcodes* para mostrar formularios y listados. Los más relevantes son:

- `[cdb_form_bar]` – formulario para crear o editar la información de un bar.
- `[cdb_form_empleado]` – formulario para crear o actualizar un empleado.
- `[cdb_form_experiencia]` – gestión de la experiencia laboral.

Inserta cualquiera de estos shortcodes en una página o entrada para mostrar el formulario correspondiente.

## Desarrollo

El código se encuentra dividido en carpetas lógicas (`includes`, `templates`, `assets`).
Los scripts de JavaScript del frontend se cargan desde `assets/js/frontend-scripts.js`.

Para más información consulta la carpeta `docs/`.
