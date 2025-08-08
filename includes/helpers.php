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
 * Obtiene las puntuaciones de gráfica por rol para un empleado.
 *
 * Intenta usar los helpers públicos del plugin cdb-grafica. Si no existen,
 * realiza una consulta directa a la tabla "grafica_empleado_results".
 * Los resultados se almacenan en un transient durante 10 minutos para
 * optimizar el rendimiento.
 *
 * @param int $empleado_id ID del empleado.
 * @return array Puntuaciones por rol. Ejemplo:
 *               [ 'empleado' => 0, 'empleador' => null, 'tutor' => null ]
 */
function cdb_form_get_grafica_scores_by_role( $empleado_id ) {
    $cache_key = 'cdb_form_card_scores_' . $empleado_id;
    $scores    = get_transient( $cache_key );

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
                $tabla    = $wpdb->prefix . 'grafica_empleado_results';
                $valores  = $wpdb->get_col( $wpdb->prepare( "SELECT total_score FROM {$tabla} WHERE post_id = %d AND user_role = %s AND total_score > 0", $empleado_id, $role ) );

                if ( ! empty( $valores ) ) {
                    $score = array_sum( $valores ) / count( $valores );
                    $score = round( $score, 1 );
                }
            }

            $scores[ $role ] = ( null !== $score ) ? floatval( $score ) : null;
        }

        set_transient( $cache_key, $scores, 10 * MINUTE_IN_SECONDS );
    }

    return $scores;
}

/**
 * Obtiene la fecha de la última valoración registrada en la gráfica.
 *
 * @param int $empleado_id ID del empleado.
 * @return string|null Fecha en formato MySQL o null si no existen registros.
 */
function cdb_form_get_last_grafica_rating_datetime( $empleado_id ) {
    if ( function_exists( 'cdb_grafica_get_last_rating_datetime' ) ) {
        return cdb_grafica_get_last_rating_datetime( $empleado_id );
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'grafica_empleado_results';

    return $wpdb->get_var( $wpdb->prepare( "SELECT MAX(created_at) FROM {$tabla} WHERE post_id = %d", $empleado_id ) );
}
