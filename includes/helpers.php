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
 * Wrapper for cdb_grafica_get_scores_by_role().
 *
 * @param int $empleado_id Employee ID.
 * @return array Scores by role: ['empleado'=>?float,'empleador'=>?float,'tutor'=>?float]
 */
function cdb_form_get_graph_scores_by_role( int $empleado_id ): array {
    $out = [ 'empleado' => null, 'empleador' => null, 'tutor' => null ];
    if ( function_exists( 'cdb_grafica_get_scores_by_role' ) && $empleado_id > 0 ) {
        $data = cdb_grafica_get_scores_by_role( $empleado_id, [] );
        foreach ( [ 'empleado', 'empleador', 'tutor' ] as $k ) {
            $out[ $k ] = isset( $data[ $k ] ) ? ( is_null( $data[ $k ] ) ? null : (float) $data[ $k ] ) : null;
        }
    }
    return $out;
}

/**
 * Wrapper for cdb_grafica_get_last_rating_datetime().
 *
 * @param int $empleado_id Employee ID.
 * @return string|null Datetime string or null.
 */
function cdb_form_get_graph_last_datetime( int $empleado_id ): ?string {
    if ( function_exists( 'cdb_grafica_get_last_rating_datetime' ) && $empleado_id > 0 ) {
        return cdb_grafica_get_last_rating_datetime( $empleado_id );
    }
    return null;
}
