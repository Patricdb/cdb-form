<?php
// Evitar acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Obtiene la fecha de la última valoración registrada para un empleado.
 *
 * @param int $empleado_id ID del empleado.
 * @return string|null Fecha de la última valoración en formato MySQL o null si no existe.
 */
function cdb_obtener_fecha_ultima_valoracion( $empleado_id ) {
    global $wpdb;
    $tabla_exp = $wpdb->prefix . 'cdb_experiencia';

    $fecha = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MAX(fecha_modificacion) FROM {$tabla_exp} WHERE empleado_id = %d",
            $empleado_id
        )
    );

    if ( empty( $fecha ) ) {
        return null;
    }

    return $fecha;
}

/**
 * Obtiene las puntuaciones de gráfica por rol para la tarjeta del empleado.
 *
 * Los resultados se cachean durante 1 minuto. Puede saltarse la caché
 * usando el parámetro `$bypass_cache` cuando se necesiten datos frescos
 * (por ejemplo, al volver de una nueva valoración).
 *
 * @param int  $empleado_id   ID del empleado.
 * @param bool $bypass_cache  Si es true se fuerza la actualización.
 * @return array Puntuaciones por rol.
 */
function cdb_form_get_card_scores( $empleado_id, $bypass_cache = false ) {
    $cache_key = 'cdb_form_card_scores_' . $empleado_id;

    // Permitir invalidaciones específicas para usuarios logueados que vuelven de una nueva valoración.
    if ( ! $bypass_cache && is_user_logged_in() ) {
        $user_id = get_current_user_id();
        if ( get_user_meta( $user_id, 'cdb_form_card_cache_invalidated', true ) ) {
            $bypass_cache = true;
            delete_user_meta( $user_id, 'cdb_form_card_cache_invalidated' );
        }
    }

    if ( $bypass_cache ) {
        delete_transient( $cache_key );
    }

    $scores = get_transient( $cache_key );

    if ( false === $scores ) {
        $scores = array();
        $roles  = array( 'empleado', 'empleador', 'tutor' );

        foreach ( $roles as $role ) {
            $score = null;

            // Preferir helpers públicos si existen.
            if ( function_exists( 'cdb_grafica_get_score_by_role' ) ) {
                $score = cdb_grafica_get_score_by_role( $empleado_id, $role );
            } elseif ( function_exists( 'cdb_grafica_get_total_score' ) && 'empleado' === $role ) {
                // Algunas instalaciones solo exponen el total genérico.
                $score = cdb_grafica_get_total_score( $empleado_id );
            } else {
                // Fallback seguro consultando la base de datos.
                global $wpdb;
                $tabla   = $wpdb->prefix . 'grafica_empleado_results';
                $valores = $wpdb->get_col( $wpdb->prepare( "SELECT total_score FROM {$tabla} WHERE post_id = %d AND user_role = %s AND total_score > 0", $empleado_id, $role ) );

                if ( ! empty( $valores ) ) {
                    $score = array_sum( $valores ) / count( $valores );
                    $score = round( $score, 1 );
                }
            }

            $scores[ $role ] = ( null !== $score ) ? floatval( $score ) : null;
        }

        set_transient( $cache_key, $scores, MINUTE_IN_SECONDS );
    }

    return $scores;
}

// Compatibilidad retro para llamadas antiguas.
function cdb_form_get_grafica_scores_by_role( $empleado_id ) {
    return cdb_form_get_card_scores( $empleado_id );
}

/**
 * Obtiene la fecha de la última valoración registrada en la gráfica para la tarjeta.
 *
 * El resultado se cachea durante 1 minuto y puede forzarse la actualización
 * con `$bypass_cache`.
 *
 * @param int  $empleado_id  ID del empleado.
 * @param bool $bypass_cache Si es true se fuerza la actualización.
 * @return string|null Fecha en formato MySQL o null si no existen registros.
 */
function cdb_form_get_card_last_rating( $empleado_id, $bypass_cache = false ) {
    $cache_key = 'cdb_form_card_last_' . $empleado_id;

    // Igual que en las puntuaciones, saltar la caché si se ha invalidado para el usuario actual.
    if ( ! $bypass_cache && is_user_logged_in() ) {
        $user_id = get_current_user_id();
        if ( get_user_meta( $user_id, 'cdb_form_card_cache_invalidated', true ) ) {
            $bypass_cache = true;
            delete_user_meta( $user_id, 'cdb_form_card_cache_invalidated' );
        }
    }

    if ( $bypass_cache ) {
        delete_transient( $cache_key );
    }

    $fecha = get_transient( $cache_key );

    if ( false === $fecha ) {
        if ( function_exists( 'cdb_grafica_get_last_rating_datetime' ) ) {
            $fecha = cdb_grafica_get_last_rating_datetime( $empleado_id );
        } else {
            global $wpdb;
            $tabla = $wpdb->prefix . 'grafica_empleado_results';
            $fecha = $wpdb->get_var( $wpdb->prepare( "SELECT MAX(created_at) FROM {$tabla} WHERE post_id = %d", $empleado_id ) );
        }

        set_transient( $cache_key, $fecha, MINUTE_IN_SECONDS );
    }

    return $fecha;
}

/**
 * Alias de compatibilidad para mantener el helper previo.
 */
function cdb_form_get_last_grafica_rating_datetime( $empleado_id ) {
    return cdb_form_get_card_last_rating( $empleado_id );
}

// Preparar invalidación de cachés cuando cdb-grafica emita el hook apropiado.
add_action( 'cdb_grafica_after_save', function( $empleado_id ) {
    delete_transient( 'cdb_form_card_scores_' . $empleado_id );
    delete_transient( 'cdb_form_card_last_' . $empleado_id );

    // Marcar que la siguiente carga debe saltar la caché para el usuario actual.
    if ( is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), 'cdb_form_card_cache_invalidated', 1 );
    }
} );
