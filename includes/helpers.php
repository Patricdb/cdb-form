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
 * @param int  $empleado_id  Employee ID.
 * @param bool $bypass_cache Whether to bypass any cache layer.
 * @return array Scores by role: ['empleado'=>float,'empleador'=>?float,'tutor'=>?float]
 */
function cdb_form_get_graph_scores_by_role( int $empleado_id, bool $bypass_cache = false ): array {
    $out = [ 'empleado' => 0.0, 'empleador' => 0.0, 'tutor' => 0.0 ];

    if ( function_exists( 'cdb_grafica_get_scores_by_role' ) && $empleado_id > 0 ) {
        $data = cdb_grafica_get_scores_by_role( $empleado_id, [ 'bypass_cache' => $bypass_cache ] );
        foreach ( [ 'empleado', 'empleador', 'tutor' ] as $k ) {
            if ( isset( $data[ $k ] ) ) {
                $out[ $k ] = is_null( $data[ $k ] ) ? null : (float) $data[ $k ];
            }
        }
    }

    return $out;
}

/**
 * Wrapper for cdb_grafica_get_last_rating_datetime().
 *
 * @param int  $empleado_id  Employee ID.
 * @param bool $bypass_cache Whether to bypass any cache layer.
 * @return string|null Datetime string or null.
 */
function cdb_form_get_graph_last_datetime( int $empleado_id, bool $bypass_cache = false ): ?string {
    if ( function_exists( 'cdb_grafica_get_last_rating_datetime' ) && $empleado_id > 0 ) {
        return cdb_grafica_get_last_rating_datetime( $empleado_id, [ 'bypass_cache' => $bypass_cache ] );
    }

    return null;
}

/**
 * Decide whether a role score line should be displayed.
 *
 * @param string     $role        Role slug.
 * @param float|null $score       Score for the role.
 * @param int        $empleado_id Employee ID.
 * @return bool
 */
function cdb_form_card_show_role_score( string $role, ?float $score, int $empleado_id ): bool {
    return (bool) apply_filters( 'cdb_form_card_show_role_score', true, $role, $score, $empleado_id );
}

/**
 * Get number of decimals for card scores.
 *
 * @return int
 */
function cdb_form_card_number_decimals(): int {
    return (int) apply_filters( 'cdb_form_card_number_decimals', 1 );
}

/**
 * Default labels for employee card lines.
 *
 * @return array
 */
function cdb_form_card_default_labels(): array {
    return [
        'empleado'    => apply_filters( 'cdb_form_label_empleado', __( 'Punt. de Gráfica por Empleados:', 'cdb-form' ) ),
        'empleador'   => apply_filters( 'cdb_form_label_empleador', __( 'Punt. de Gráfica por Empleadores:', 'cdb-form' ) ),
        'tutor'       => apply_filters( 'cdb_form_label_tutor', __( 'Punt. de Gráfica por Tutores:', 'cdb-form' ) ),
        'experiencia' => apply_filters( 'cdb_form_label_experiencia', __( 'Punt. de Experiencia:', 'cdb-form' ) ),
        'total'       => apply_filters( 'cdb_form_label_total', __( 'Puntuación Total:', 'cdb-form' ) ),
        'ultima'      => apply_filters( 'cdb_form_label_ultima', __( 'Última valoración:', 'cdb-form' ) ),
    ];
}

/**
 * Retrieve labels for employee card lines allowing customization.
 *
 * @return array
 */
function cdb_form_card_labels(): array {
    return (array) apply_filters( 'cdb_form_card_labels', cdb_form_card_default_labels() );
}

// Tratar el score de entrada como porcentaje 0–100 para las barras de nivel.
add_filter( 'cdb_form_niveles_max_score', function( $max ) {
    return 100;
} );
