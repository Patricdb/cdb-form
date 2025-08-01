<?php
/**
 * Lógica de desinstalación para el plugin cdb-form.
 *
 * - Elimina la tabla personalizada wp_cdb_experiencia si existe.
 * - Borra los metadatos asociados a los CPTs bar, cdb_posiciones y empleado:
 *   bar_id, posicion_id, cdb_experiencia_score y disponible.
 */

// Asegurarse de que WordPress está cargando el archivo correctamente.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// Obtener acceso a $wpdb para ejecutar consultas.
global $wpdb;

// 1. Eliminar la tabla personalizada de experiencia.
$table = $wpdb->prefix . 'cdb_experiencia';
$wpdb->query( "DROP TABLE IF EXISTS $table" );

// 2. Borrar metadatos relacionados con bares, posiciones y empleados.
$meta_keys = array( 'bar_id', 'posicion_id', 'cdb_experiencia_score', 'disponible' );
$placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );

$wpdb->query( $wpdb->prepare( "
    DELETE pm FROM {$wpdb->postmeta} pm
    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
    WHERE pm.meta_key IN ($placeholders)
      AND p.post_type IN ('bar','cdb_posiciones','empleado')
", $meta_keys ) );

