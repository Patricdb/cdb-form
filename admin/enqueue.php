<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar estilos y scripts en el panel de administración
function cdb_form_admin_enqueue( $hook ) {
    // Cargar solo en las páginas del plugin
    if ( strpos( $hook, 'cdb-form' ) === false ) {
        return;
    }

    // No se requieren dependencias adicionales en la página de configuración
    if ( 'toplevel_page_cdb-form-disenio-empleado' === $hook ) {
        // Se podrían añadir estilos propios aquí en el futuro
    }
}
add_action( 'admin_enqueue_scripts', 'cdb_form_admin_enqueue' );
