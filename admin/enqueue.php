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

    // Cargar el color picker en la página de diseño
    if ( 'toplevel_page_cdb-form-disenio-empleado' === $hook ) {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }
}
add_action( 'admin_enqueue_scripts', 'cdb_form_admin_enqueue' );
