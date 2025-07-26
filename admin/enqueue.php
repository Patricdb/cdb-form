<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar estilos y scripts en el panel de administración
function cdb_form_admin_enqueue( $hook ) {
    // Cargar solo en las páginas del plugin
    if ( strpos( $hook, 'cdb_form' ) === false ) {
        return;
    }

    // Estilos
    wp_enqueue_style( 'cdb-form-admin-style', CDB_FORM_URL . 'assets/css/admin-styles.css', array(), '1.0', 'all' );

    // Scripts
    wp_enqueue_script( 'cdb-form-admin-script', CDB_FORM_URL . 'assets/js/admin-scripts.js', array( 'jquery' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'cdb_form_admin_enqueue' );
