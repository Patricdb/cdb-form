<?php
// Evitar acceso directo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gestiona los tipos de avisos (nombre, clase y color) del sistema.
 *
 * Se usa una opción de WordPress para permitir que el administrador añada,
 * modifique o elimine tipos de avisos desde el panel. Este sistema está
 * preparado para escalar a más tipos de avisos en el futuro y puede ser
 * extendido por otros plugins mediante las funciones aquí definidas.
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
        ),
        'info' => array(
            'name'  => __( 'Info', 'cdb-form' ),
            'class' => 'cdb-aviso--info',
            'color' => '#2563eb',
        ),
        'exito' => array(
            'name'  => __( 'Éxito', 'cdb-form' ),
            'class' => 'cdb-aviso--exito',
            'color' => '#16a34a',
        ),
    );

    $stored = get_option( 'cdb_form_tipos_color', array() );
    if ( empty( $stored ) || ! is_array( $stored ) ) {
        $stored = $defaults;
    }

    return $stored;
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
 * @param array  $args  {name, class, color}
 */
function cdb_form_register_tipo_color( $slug, $args ) {
    $tipos = cdb_form_get_tipos_color();
    $slug  = sanitize_key( $slug );

    $tipos[ $slug ] = wp_parse_args(
        array(
            'name'  => isset( $args['name'] ) ? sanitize_text_field( $args['name'] ) : $slug,
            'class' => isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : 'cdb-aviso--' . $slug,
            'color' => isset( $args['color'] ) ? sanitize_hex_color( $args['color'] ) : '#000000',
        )
    );

    update_option( 'cdb_form_tipos_color', $tipos );
}
