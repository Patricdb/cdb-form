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

/**
 * Obtiene el valor de la primera opción existente entre varias claves.
 *
 * El primer elemento del array se considera el nombre canónico de la opción.
 * Si se encuentra un valor en alguna de las claves alternativas, se migrará a
 * la clave canónica para mantener la coherencia de nomenclatura.
 *
 * @param array  $keys    Lista de posibles nombres de opción.
 * @param string $default Valor por defecto si ninguna opción existe.
 * @return string
 */
function cdb_form_get_option_compat( $keys, $default = '' ) {
    if ( empty( $keys ) || ! is_array( $keys ) ) {
        return $default;
    }

    $canonical = array_shift( $keys );
    $value     = get_option( $canonical, null );

    if ( null !== $value && '' !== $value ) {
        return $value;
    }

    foreach ( $keys as $alt_key ) {
        $value = get_option( $alt_key, null );
        if ( null !== $value && '' !== $value ) {
            // Migrar el valor a la clave canónica para futuras llamadas.
            update_option( $canonical, $value );
            return $value;
        }
    }

    return $default;
}

/**
 * Obtiene y renderiza un mensaje configurable usando una clave canónica.
 *
 * Permite mantener compatibilidad con nombres de opciones antiguos
 * migrándolos automáticamente a la nueva nomenclatura.
 *
 * @param string $slug         Clave del mensaje.
 * @param string $default_text Texto por defecto si no hay opción guardada.
 * @param string $default_tipo Tipo/color por defecto.
 * @return string HTML del mensaje listo para mostrarse.
 */
function cdb_form_get_mensaje( $slug, $default_text = '', $default_tipo = 'aviso' ) {
    $map = array(
        'cdb_aviso_sin_puntuacion'     => 'cdb_mensaje_puntuacion_no_disponible',
        'cdb_empleado_no_encontrado'   => 'cdb_mensaje_empleado_no_encontrado',
        'cdb_experiencia_sin_perfil'   => 'cdb_mensaje_experiencia_sin_perfil',
        'cdb_bares_sin_resultados'     => 'cdb_mensaje_busqueda_sin_bares',
        'cdb_empleados_vacio'          => 'cdb_mensaje_sin_empleados',
        'cdb_empleados_sin_resultados' => 'cdb_mensaje_busqueda_sin_empleados',
        'cdb_acceso_sin_login'         => 'cdb_mensaje_login_requerido',
        'cdb_acceso_sin_permisos'      => 'cdb_mensaje_sin_permiso',
    );

    $text_option  = $slug;
    $color_option = 'cdb_color_' . $slug;

    if ( isset( $map[ $slug ] ) ) {
        $old_text_option  = $map[ $slug ];
        $old_color_option = str_replace( 'cdb_mensaje_', 'cdb_color_', $old_text_option );

        // Migrar valores antiguos a las nuevas claves canónicas.
        cdb_form_get_option_compat( array( $text_option, $old_text_option ), $default_text );
        cdb_form_get_option_compat( array( $color_option, $old_color_option ), $default_tipo );
    }

    return cdb_form_render_mensaje( $text_option, $color_option, $default_text, $default_tipo );
}

/**
 * Renderiza un mensaje configurado desde las opciones del plugin.
 *
 * @param string $text_option  Nombre de la opción que almacena el texto.
 * @param string $color_option Nombre de la opción que almacena el tipo/color.
 * @param string $default_text Texto por defecto si no hay opción guardada.
 * @param string $default_tipo Tipo/color por defecto.
 * @return string HTML del mensaje listo para mostrarse.
 */
function cdb_form_render_mensaje( $text_option, $color_option, $default_text, $default_tipo = 'aviso' ) {
    $texto      = cdb_form_get_option_compat(
        array(
            $text_option,
            $text_option . '_destacado',
            $text_option . '_principal',
            $text_option . '_mensaje_destacado',
            $text_option . '_mensaje_principal',
            $text_option . '_frase_destacada',
            $text_option . '_frase_principal',
            $text_option . '_featured',
            $text_option . '_primary',
            $text_option . '_highlight',
        ),
        $default_text
    );
    $secundario = cdb_form_get_option_compat(
        array(
            $text_option . '_secundaria',
            $text_option . '_secundario',
            $text_option . '_mensaje_secundario',
            $text_option . '_mensaje_secundaria',
            $text_option . '_frase_secundaria',
            $text_option . '_frase_secundario',
            $text_option . '_secondary',
        ),
        ''
    );
    $tipo       = get_option( $color_option, $default_tipo );
    $clase      = cdb_form_get_tipo_color_class( $tipo );

    // Si no hay frase secundaria configurada, intentar dividir el texto principal
    // en dos frases usando distintos delimitadores (\n, <br> o signos de puntuación).
    if ( empty( $secundario ) && is_string( $texto ) ) {
        $partes = array();

        // Permitir salto de línea manual mediante <br>.
        if ( preg_match( '/<br\s*\/?>/i', $texto ) ) {
            $partes = preg_split( '/<br\s*\/?>/i', $texto, 2 );
        // Permitir salto de línea manual mediante retorno de carro.
        } elseif ( strpos( $texto, "\n" ) !== false ) {
            $partes = preg_split( "/\r?\n/", $texto, 2 );
        } else {
            // Separación automática tras signos de puntuación comunes.
            $partes = preg_split( '/(?<=[\.!?;:])\s+/', $texto, 2 );
        }

        if ( isset( $partes[1] ) ) {
            $texto      = trim( $partes[0] );
            $secundario = trim( $partes[1] );
        }
    }

    $html  = '<div class="cdb-aviso ' . esc_attr( $clase ) . '">';
    $html .= '<strong class="cdb-mensaje-destacado">' . wp_kses_post( $texto ) . '</strong>';

    if ( '' !== $secundario ) {
        $html .= '<br><span class="cdb-mensaje-secundario">' . wp_kses_post( $secundario ) . '</span>';
    }

    $html .= '</div>';

    return $html;
}
