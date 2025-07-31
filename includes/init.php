<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- NUEVO: Incluir el archivo del CPT Equipo (ajusta la ruta a la ubicación real) ---
if ( file_exists( WP_PLUGIN_DIR . '/cdb-bar/includes/cpt-equipo.php' ) ) {
    require_once WP_PLUGIN_DIR . '/cdb-bar/includes/cpt-equipo.php';
} else {
    // Opcional: registrar un error si no se encuentra
    error_log("No se encontró cpt-equipo.php; la relación Equipo podría no funcionar.");
}

// Incluir archivos esenciales del plugin
require_once CDB_FORM_PATH . 'includes/form-handler.php';
require_once CDB_FORM_PATH . 'includes/validations.php';
require_once CDB_FORM_PATH . 'includes/capabilities.php';
require_once CDB_FORM_PATH . 'includes/shortcodes.php';
require_once CDB_FORM_PATH . 'includes/ajax-functions.php';

// Cargar scripts y estilos para el admin y frontend
require_once CDB_FORM_PATH . 'admin/enqueue.php';
require_once CDB_FORM_PATH . 'admin/menu.php';
require_once CDB_FORM_PATH . 'public/enqueue.php';

// Acción de inicialización del plugin
function cdb_form_init() {
    // Cargar las traducciones del plugin
    load_plugin_textdomain(
        'cdb-form',
        false,
        dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages'
    );
    // Aquí se pueden añadir más acciones de inicialización en el futuro
}
add_action( 'plugins_loaded', 'cdb_form_init' );

// Filtro para mostrar la disponibilidad en la vista del contenido "Empleado"
function cdb_mostrar_disponibilidad_empleado($content) {
    if (is_singular('empleado')) {
        $empleado_id = get_the_ID();
        $disponible = get_post_meta($empleado_id, 'disponible', true);
        
        // Registrar en el log para verificar
        error_log("Empleado ID: $empleado_id - Disponible: " . ($disponible === "" ? "No definido" : $disponible));

        // Si el valor es vacío o no está definido, mostrar "No Disponible"
        if ($disponible === "" || $disponible === null) {
            $disponible_texto = 'No Disponible';
        } else {
            $disponible_texto = ($disponible == 1) ? 'Disponible' : 'No Disponible';
        }

        $info_disponibilidad = "<p><strong>Estado:</strong> $disponible_texto</p>";

        return $info_disponibilidad . $content;
    }
    return $content;
}
add_filter('the_content', 'cdb_mostrar_disponibilidad_empleado');
