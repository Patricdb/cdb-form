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

// Valores por defecto de mensajes y avisos.
$cdb_form_defaults = array(
    'cdb_aviso_sin_puntuacion'     => __( 'Puntuación gráfica no disponible.', 'cdb-form' ) . '|' . __( 'Añade más valoraciones para generar tu gráfico.', 'cdb-form' ),
    'cdb_empleado_no_encontrado'   => __( 'Empleado no encontrado.', 'cdb-form' ) . '|' . __( 'Crea primero tu perfil de empleado para continuar.', 'cdb-form' ),
    'cdb_experiencia_sin_perfil'   => __( 'Para registrar experiencia debes crear tu perfil.', 'cdb-form' ) . '|' . __( 'Completa tu información de empleado y vuelve aquí.', 'cdb-form' ),
    'cdb_bares_sin_resultados'     => __( 'No hay bares que coincidan con tu búsqueda.', 'cdb-form' ) . '|' . __( 'Ajusta filtros o prueba con otro término.', 'cdb-form' ),
    'cdb_empleados_vacio'          => __( 'Aún no hay empleados registrados.', 'cdb-form' ) . '|' . __( '¡Sé el primero en unirte al proyecto!', 'cdb-form' ),
    'cdb_empleados_sin_resultados' => __( 'Sin coincidencias para tu búsqueda.', 'cdb-form' ) . '|' . __( 'Modifica los criterios e inténtalo de nuevo.', 'cdb-form' ),
    'cdb_acceso_sin_login'         => __( 'Debes iniciar sesión para acceder.', 'cdb-form' ) . '|' . __( 'Inicia sesión o regístrate para continuar.', 'cdb-form' ),
    'cdb_acceso_sin_permisos'      => __( 'No tienes permisos para ver este contenido.', 'cdb-form' ) . '|' . __( 'Contacta con un admin si crees que es un error.', 'cdb-form' ),
    'cdb_ajax_exito_empleado'      => __( 'Empleado creado correctamente.', 'cdb-form' ) . '|' . __( 'El perfil se ha guardado sin problemas.', 'cdb-form' ),
    'cdb_ajax_error_empleado'      => __( 'Error al crear empleado.', 'cdb-form' ) . '|' . __( 'Inténtalo de nuevo más tarde.', 'cdb-form' ),
    'cdb_ajax_exito_experiencia'   => __( 'Experiencia registrada.', 'cdb-form' ) . '|' . __( 'Se ha guardado la experiencia.', 'cdb-form' ),
    'cdb_ajax_empleados_sin_resultados' => __( 'Sin resultados.', 'cdb-form' ) . '|' . __( 'No hay empleados que coincidan con tu búsqueda.', 'cdb-form' ),
    'cdb_ajax_bares_sin_resultados'     => __( 'Sin resultados.', 'cdb-form' ) . '|' . __( 'No hay bares que coincidan con tu búsqueda.', 'cdb-form' ),
    'cdb_ajax_disponibilidad_actualizada' => __( 'Disponibilidad actualizada correctamente.', 'cdb-form' ) . '|' . __( 'Los datos se han guardado.', 'cdb-form' ),
    'cdb_ajax_error_disponibilidad' => __( 'Hubo un problema al actualizar la disponibilidad.', 'cdb-form' ) . '|' . __( 'Inténtalo de nuevo más tarde.', 'cdb-form' ),
    'cdb_ajax_estado_bar_actualizado' => __( 'Estado del bar actualizado correctamente.', 'cdb-form' ) . '|' . __( 'Los datos se han guardado.', 'cdb-form' ),
    'cdb_ajax_error_estado_bar'    => __( 'Hubo un problema al actualizar el estado del bar.', 'cdb-form' ) . '|' . __( 'Inténtalo de nuevo más tarde.', 'cdb-form' ),
    'cdb_ajax_error_comunicacion'  => __( 'Error de comunicación.', 'cdb-form' ) . '|' . __( 'No se pudo contactar con el servidor.', 'cdb-form' ),
    'cdb_ajax_error_anio_cifras'   => __( 'El año debe tener 4 cifras.', 'cdb-form' ) . '|' . __( 'Introduce un año válido.', 'cdb-form' ),
    'cdb_ajax_error_nombre_invalido' => __( 'Selecciona un nombre válido.', 'cdb-form' ) . '|' . __( 'Elige una opción de la lista.', 'cdb-form' ),
    'cdb_ajax_error_posicion_invalida' => __( 'Selecciona una posición válida.', 'cdb-form' ) . '|' . __( 'Usa la ayuda de autocompletado.', 'cdb-form' ),
    'cdb_ajax_error_bar_invalido'  => __( 'Selecciona un bar válido.', 'cdb-form' ) . '|' . __( 'Usa la ayuda de autocompletado.', 'cdb-form' ),
    'cdb_ajax_error_anio_invalido' => __( 'Selecciona un año válido.', 'cdb-form' ) . '|' . __( 'Usa un formato de cuatro cifras.', 'cdb-form' ),
    'cdb_ajax_error_zona_invalida' => __( 'Selecciona una zona válida.', 'cdb-form' ) . '|' . __( 'Elige una opción de la lista.', 'cdb-form' ),
    'cdb_mensaje_bienvenida'            => __( 'Gracias por colaborar con el Proyecto CdB.', 'cdb-form' ) . '|' . __( 'Explora las opciones disponibles.', 'cdb-form' ),
    'cdb_mensaje_bienvenida_usuario'    => __( 'No tienes un perfil de empleado registrado.', 'cdb-form' ) . '|' . __( 'Crea tu perfil para comenzar.', 'cdb-form' ),
    'cdb_mensaje_empleado_sin_experiencia' => __( 'Aún no has registrado ninguna experiencia laboral.', 'cdb-form' ) . '|' . __( 'Añade tus puestos anteriores para completar tu perfil.', 'cdb-form' ),
    'cdb_mensaje_posicion_no_valida'    => __( 'Error: No se ha proporcionado una posición válida.', 'cdb-form' ) . '|' . __( 'Selecciona una posición para continuar.', 'cdb-form' ),
    'cdb_mensaje_bar_sin_registro'      => __( 'No tienes un bar registrado.', 'cdb-form' ) . '|' . __( 'Crea uno antes de actualizar su estado.', 'cdb-form' ),
    'cdb_mensaje_disponibilidad_sin_perfil' => __( 'No tienes un perfil de empleado.', 'cdb-form' ) . '|' . __( 'Crea uno antes de actualizar tu disponibilidad.', 'cdb-form' ),
    // …añade aquí cualquier clave nueva que surja
);

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
function cdb_form_get_mensaje( $clave, $tipo = 'aviso' ) {
    global $cdb_form_defaults;

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

    $text_option  = $clave;
    $color_option = 'cdb_color_' . $clave;
    $show_option  = $text_option . '_mostrar';

    if ( isset( $map[ $clave ] ) ) {
        $old_text_option  = $map[ $clave ];
        $old_color_option = str_replace( 'cdb_mensaje_', 'cdb_color_', $old_text_option );
        // Migrar valores antiguos a las nuevas claves canónicas.
        cdb_form_get_option_compat( array( $text_option, $old_text_option ), null );
        cdb_form_get_option_compat( array( $color_option, $old_color_option ), null );

        // Migrar la frase secundaria y visibilidad
        cdb_form_get_option_compat(
            array( $text_option . '_secundaria', $old_text_option . '_secundaria' ),
            null
        );
        cdb_form_get_option_compat(
            array( $show_option, $old_text_option . '_mostrar' ),
            null
        );
    }

    $texto      = get_option( $text_option, '' );
    $secundario = get_option( $text_option . '_secundaria', '' );
    $mostrar    = get_option( $show_option, '1' );
    if ( '' === $texto ) {
        $texto = $cdb_form_defaults[ $clave ] ?? __( 'Aviso no configurado', 'cdb-form' );
    }
    if ( '' === $secundario && strpos( $texto, '|' ) !== false ) {
        $parts      = array_map( 'trim', explode( '|', $texto, 2 ) );
        $texto      = $parts[0];
        $secundario = $parts[1] ?? '';
    }

    if ( '0' === $mostrar ) {
        return '';
    }

    $tipo_guardado = get_option( $color_option, $tipo );
    $clase         = cdb_form_get_tipo_color_class( $tipo_guardado );

    $html  = '<div class="cdb-aviso ' . esc_attr( $clase ) . '">';
    $html .= '<strong class="cdb-mensaje-destacado">' . wp_kses_post( $texto ) . '</strong>';
    if ( '' !== $secundario ) {
        $html .= '<span class="cdb-mensaje-secundario">' . wp_kses_post( $secundario ) . '</span>';
    }
    $html .= '</div>';

    return $html;
}

/**
 * Devuelve un mensaje configurable como texto plano para uso en JavaScript.
 *
 * @param string $clave Clave del mensaje.
 * @return string Texto plano del mensaje.
 */
function cdb_form_get_mensaje_js( $clave ) {
    global $cdb_form_defaults;

    $default   = $cdb_form_defaults[ $clave ] ?? '';
    $texto     = cdb_form_get_option_compat(
        array(
            $clave,
            $clave . '_destacado',
            $clave . '_principal',
            $clave . '_mensaje_destacado',
            $clave . '_mensaje_principal',
            $clave . '_frase_destacada',
            $clave . '_frase_principal',
            $clave . '_featured',
            $clave . '_primary',
            $clave . '_highlight',
        ),
        $default
    );
    $secundario = cdb_form_get_option_compat(
        array(
            $clave . '_secundaria',
            $clave . '_secundario',
            $clave . '_mensaje_secundario',
            $clave . '_mensaje_secundaria',
            $clave . '_frase_secundaria',
            $clave . '_frase_secundario',
            $clave . '_secondary',
        ),
        ''
    );

    if ( '' === $secundario && strpos( $texto, '|' ) !== false ) {
        $parts = array_map( 'trim', explode( '|', $texto, 2 ) );
        $texto = $parts[0];
        $secundario = $parts[1] ?? '';
    }

    $mensaje = trim( wp_strip_all_tags( $texto ) );
    if ( '' !== $secundario ) {
        $mensaje .= '|' . trim( wp_strip_all_tags( $secundario ) );
    }

    $mostrar = get_option( $clave . '_mostrar', '1' );
    if ( '0' === $mostrar ) {
        return '';
    }

    return $mensaje;
}

/**
 * Devuelve un mensaje traducido según el idioma actual de WordPress.
 *
 * @param string $key Clave del mensaje.
 * @return string Mensaje traducido.
 */
function cdb_form_get_mensaje_i18n( $key ) {
    $msg = cdb_form_get_mensaje_js( $key );
    list( $dest, $sec ) = array_pad( explode( '|', $msg, 2 ), 2, '' );
    return trim( sprintf( '%s %s', __( $dest, 'cdb-form' ), __( $sec, 'cdb-form' ) ) );
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
        $html .= '<span class="cdb-mensaje-secundario">' . wp_kses_post( $secundario ) . '</span>';
    }

    $html .= '</div>';

    return $html;
}
