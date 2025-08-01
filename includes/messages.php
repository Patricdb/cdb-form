<?php
// Evitar acceso directo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gestiona los tipos de avisos (nombre, clase y colores) del sistema.
 *
 * Se usa una opción de WordPress para permitir que el administrador añada,
 * modifique o elimine tipos de avisos desde el panel. Este sistema está
 * preparado para escalar a más tipos de avisos en el futuro y puede ser
 * extendido por otros plugins mediante las funciones aquí definidas.
 *
 * A partir de la versión 1.1 los tipos de aviso pueden definir tanto color
 * de fondo como color de texto. Los valores antiguos que sólo almacenaban el
 * color de fondo siguen siendo compatibles y se les asignará un color de
 * texto recomendado automáticamente.
 */

/**
 * Obtiene la lista de tipos de avisos disponibles.
 *
 * @return array
 */
function cdb_form_get_tipos_color() {
    $defaults = array(
        'aviso' => array(
            'name'  => __( 'Aviso', 'cdb-form' ),
            'class' => 'cdb-aviso--aviso',
            'color' => '#dc2626',
            'text'  => '#ffffff',
        ),
        'info' => array(
            'name'  => __( 'Info', 'cdb-form' ),
            'class' => 'cdb-aviso--info',
            'color' => '#2563eb',
            'text'  => '#ffffff',
        ),
        'exito' => array(
            'name'  => __( 'Éxito', 'cdb-form' ),
            'class' => 'cdb-aviso--exito',
            'color' => '#16a34a',
            'text'  => '#ffffff',
        ),
    );

    $stored = get_option( 'cdb_form_tipos_color', array() );
    if ( empty( $stored ) || ! is_array( $stored ) ) {
        $stored = $defaults;
    }

    // Asegurar claves y compatibilidad con versiones anteriores.
    foreach ( $stored as $slug => $info ) {
        if ( empty( $info['color'] ) && isset( $defaults[ $slug ]['color'] ) ) {
            $info['color'] = $defaults[ $slug ]['color'];
        }
        if ( empty( $info['text'] ) ) {
            $bg          = $info['color'] ?? '#000000';
            $info['text'] = cdb_form_get_contrasting_text_color( $bg );
        }
        $stored[ $slug ] = $info;
    }

    return $stored;
}

/**
 * Calcula un color de texto (negro/blanco) legible sobre un color de fondo.
 *
 * @param string $hex Color de fondo en formato hexadecimal.
 * @return string Color de texto recomendado.
 */
function cdb_form_get_contrasting_text_color( $hex ) {
    $hex = ltrim( $hex, '#' );
    if ( strlen( $hex ) === 3 ) {
        $hex = preg_replace( '/(.)/', '$1$1', $hex );
    }
    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );
    $luminance = ( $r * 299 + $g * 587 + $b * 114 ) / 1000;
    return ( $luminance > 128 ) ? '#000000' : '#ffffff';
}

/**
 * Devuelve la clase CSS de un tipo de aviso.
 *
 * @param string $slug Identificador del tipo.
 * @return string
 */
function cdb_form_get_tipo_color_class( $slug ) {
    $tipos = cdb_form_get_tipos_color();
    return isset( $tipos[ $slug ] ) ? $tipos[ $slug ]['class'] : '';
}

/**
 * Registra programáticamente un nuevo tipo de aviso.
 *
 * @param string $slug  Identificador único.
 * @param array  $args  {name, class, color, text}
 */
function cdb_form_register_tipo_color( $slug, $args ) {
    $tipos = cdb_form_get_tipos_color();
    $slug  = sanitize_key( $slug );

    $tipos[ $slug ] = wp_parse_args(
        array(
            'name'  => isset( $args['name'] ) ? sanitize_text_field( $args['name'] ) : $slug,
            'class' => isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : 'cdb-aviso--' . $slug,
            'color' => isset( $args['color'] ) ? sanitize_hex_color( $args['color'] ) : '#000000',
            'text'  => isset( $args['text'] ) ? sanitize_hex_color( $args['text'] ) : cdb_form_get_contrasting_text_color( $args['color'] ?? '#000000' ),
        )
    );

    update_option( 'cdb_form_tipos_color', $tipos );
}
