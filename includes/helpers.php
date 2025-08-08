<?php
// Evitar acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Obtiene la fecha de la última valoración registrada para un empleado.
 *
 * Si el plugin `cdb-grafica` está activo y provee la función
 * `cdb_grafica_get_last_rating_datetime()`, se utiliza dicha función.
 * En caso contrario, se consulta directamente la tabla
 * `{prefix}grafica_empleado_results` de forma segura.
 *
 * @param int $empleado_id ID del empleado.
 * @return string|null Fecha de la última valoración en formato MySQL o null si no existe.
 */
function cdb_obtener_fecha_ultima_valoracion( $empleado_id ) {
    if ( function_exists( 'cdb_grafica_get_last_rating_datetime' ) ) {
        $fecha = cdb_grafica_get_last_rating_datetime( $empleado_id );
        return $fecha ? $fecha : null;
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'grafica_empleado_results';

    $fecha = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MAX(created_at) FROM {$tabla} WHERE empleado_id = %d",
            $empleado_id
        )
    );

    return $fecha ? $fecha : null;
}

/**
 * Obtiene la puntuación promedio de la gráfica para un rol específico.
 *
 * @param int    $empleado_id ID del empleado.
 * @param string $rol         Rol del evaluador (empleado, empleador, tutor).
 * @return float|null         Puntuación promedio o null si no hay registros.
 */
function cdb_obtener_puntuacion_grafica_por_rol( $empleado_id, $rol ) {
    if ( function_exists( 'cdb_grafica_get_average_score_by_role' ) ) {
        $score = cdb_grafica_get_average_score_by_role( $empleado_id, $rol );
        return is_null( $score ) ? null : floatval( $score );
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'grafica_empleado_results';

    $score = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT AVG(puntuacion_total) FROM {$tabla} WHERE empleado_id = %d AND rol = %s",
            $empleado_id,
            $rol
        )
    );

    return is_null( $score ) ? null : round( floatval( $score ), 1 );
}
