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
