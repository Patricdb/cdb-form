<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Encola los recursos necesarios en el frontal del sitio.
 *
 * Además del script principal (registrado para cargarse solo cuando se
 * necesite), aquí se encola la hoja de estilos base para los mensajes y se
 * generan reglas dinámicas para cada tipo/color configurable por el
 * administrador. A partir de la versión 1.1 estas reglas incluyen tanto el
 * color de fondo como el de texto, de modo que los shortcodes que imprimen
 * avisos pueden usar clases como <code>cdb-aviso--aviso</code> o cualquier
 * otra definida en la pantalla de ajustes y mantener los colores elegidos.
 */
function cdb_form_public_enqueue() {
    // Registrar el script sin encolarlo; se encolará condicionalmente.
    wp_register_script(
        'cdb-form-frontend-script',
        CDB_FORM_URL . 'assets/js/frontend-scripts.js',
        array( 'jquery' ),
        '1.0',
        true
    );

    // Hoja de estilos compartida entre el admin y el frontend.
    wp_enqueue_style(
        'cdb-form-config-mensajes',
        CDB_FORM_URL . 'assets/css/config-mensajes.css',
        array(),
        '1.0'
    );

    // Generar las reglas CSS para cada tipo/color definido.
    $tipos = cdb_form_get_tipos_color();
    $css   = '';
    foreach ( $tipos as $info ) {
        $class = isset( $info['class'] ) ? sanitize_html_class( $info['class'] ) : '';
        $bg    = isset( $info['color'] ) ? sanitize_hex_color( $info['color'] ) : '';
        $text  = isset( $info['text'] ) ? sanitize_hex_color( $info['text'] ) : '';
        if ( ! $class || ! $bg ) {
            continue;
        }
        if ( ! $text ) {
            $text = cdb_form_get_contrasting_text_color( $bg );
        }
        $css .= sprintf( '.%1$s{border-left-color:%2$s;background-color:%2$s;color:%3$s;}', $class, $bg, $text );
    }

    if ( $css ) {
        wp_add_inline_style( 'cdb-form-config-mensajes', $css );
    }

    // Pasar AJAX URL y Nonce a JavaScript.
    wp_localize_script( 'cdb-form-frontend-script', 'cdb_form_ajax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'cdb_form_nonce' ), // Agregamos el nonce
    ) );

    // Pasar mensajes configurables a JavaScript.
    wp_localize_script(
        'cdb-form-frontend-script',
        'cdbMsgs',
        array(
            'cdb_ajax_exito_empleado'       => cdb_form_get_mensaje_js( 'cdb_ajax_exito_empleado' ),
            'cdb_ajax_error_empleado'       => cdb_form_get_mensaje_js( 'cdb_ajax_error_empleado' ),
            'cdb_ajax_exito_experiencia'    => cdb_form_get_mensaje_js( 'cdb_ajax_exito_experiencia' ),
            'cdb_ajax_empleados_sin_resultados' => cdb_form_get_mensaje_js( 'cdb_ajax_empleados_sin_resultados' ),
            'cdb_ajax_bares_sin_resultados'     => cdb_form_get_mensaje_js( 'cdb_ajax_bares_sin_resultados' ),
            'cdb_ajax_disponibilidad_actualizada' => cdb_form_get_mensaje_js( 'cdb_ajax_disponibilidad_actualizada' ),
            'cdb_ajax_error_disponibilidad' => cdb_form_get_mensaje_js( 'cdb_ajax_error_disponibilidad' ),
            'cdb_ajax_estado_bar_actualizado' => cdb_form_get_mensaje_js( 'cdb_ajax_estado_bar_actualizado' ),
            'cdb_ajax_error_estado_bar'     => cdb_form_get_mensaje_js( 'cdb_ajax_error_estado_bar' ),
            'cdb_ajax_error_comunicacion'   => cdb_form_get_mensaje_js( 'cdb_ajax_error_comunicacion' ),
            'cdb_ajax_error_anio_cifras'    => cdb_form_get_mensaje_js( 'cdb_ajax_error_anio_cifras' ),
            'cdb_ajax_error_nombre_invalido' => cdb_form_get_mensaje_js( 'cdb_ajax_error_nombre_invalido' ),
            'cdb_ajax_error_posicion_invalida' => cdb_form_get_mensaje_js( 'cdb_ajax_error_posicion_invalida' ),
            'cdb_ajax_error_bar_invalido'   => cdb_form_get_mensaje_js( 'cdb_ajax_error_bar_invalido' ),
            'cdb_ajax_error_anio_invalido'  => cdb_form_get_mensaje_js( 'cdb_ajax_error_anio_invalido' ),
            'cdb_ajax_error_zona_invalida'  => cdb_form_get_mensaje_js( 'cdb_ajax_error_zona_invalida' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'cdb_form_public_enqueue' );
