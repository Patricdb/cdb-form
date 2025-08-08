<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar estilos y scripts en el panel de administración.
// La hoja de estilos "config-mensajes.css" se comparte con el frontend
// (ver public/enqueue.php) para que las clases de aviso definidas en la
// configuración tengan estilo también para los usuarios.
function cdb_form_admin_enqueue( $hook ) {
    // Cargar solo en las páginas del plugin
    if ( strpos( $hook, 'cdb-form' ) === false ) {
        return;
    }

    // Recursos para la configuración de mensajes y avisos
    if ( 'cdb-form_page_cdb-form-config-mensajes' === $hook ) {
        wp_enqueue_style(
            'cdb-form-config-mensajes',
            CDB_FORM_URL . 'assets/css/config-mensajes.css',
            array(),
            '1.0'
        );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script(
            'cdb-form-config-mensajes',
            CDB_FORM_URL . 'assets/js/config-mensajes.js',
            array( 'jquery', 'wp-color-picker' ),
            '1.0',
            true
        );
        wp_localize_script(
            'cdb-form-config-mensajes',
            'cdbMensajes',
            array(
                'nuevoNombre'   => __( 'Nombre', 'cdb-form' ),
                'nuevaClase'    => __( 'Clase CSS', 'cdb-form' ),
                'eliminar'      => __( 'Eliminar', 'cdb-form' ),
                'contrasteBajo' => __( 'Contraste bajo', 'cdb-form' ),
            )
        );
    }
}
add_action( 'admin_enqueue_scripts', 'cdb_form_admin_enqueue' );
