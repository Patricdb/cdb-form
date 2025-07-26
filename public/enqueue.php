<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar estilos y scripts en el frontend
function cdb_form_public_enqueue() {
    // Estilos
    wp_enqueue_style( 'cdb-form-frontend-style', CDB_FORM_URL . 'assets/css/frontend-styles.css', array(), '1.0', 'all' );

    // Scripts
    wp_enqueue_script( 'cdb-form-frontend-script', CDB_FORM_URL . 'assets/js/frontend-scripts.js', array( 'jquery' ), '1.0', true );

    // Pasar AJAX URL y Nonce a JavaScript
    wp_localize_script( 'cdb-form-frontend-script', 'cdb_form_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('cdb_form_nonce') // Agregamos el nonce
    ));
}
add_action( 'wp_enqueue_scripts', 'cdb_form_public_enqueue' );
