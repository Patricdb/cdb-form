<?php
require_once('../../../wp-load.php'); // Asegurar carga de WordPress

if (!is_user_logged_in()) {
    die( __( 'Debes iniciar sesión.', 'cdb-form' ) );
}

$current_user = wp_get_current_user();
global $cdb_empleado_id, $wpdb;

// Obtener la ID del empleado
if (!$cdb_empleado_id) {
    $cdb_empleado_id = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} 
         WHERE post_type = 'empleado' 
         AND post_author = %d 
         AND post_status = 'publish' 
         ORDER BY post_date DESC 
         LIMIT 1",
        $current_user->ID
    ));
}

// Mostrar resultados
echo "<h1>Test de Variable Global</h1>";
echo "<p><strong>Usuario:</strong> " . esc_html($current_user->display_name) . "</p>";
echo "<p><strong>Empleado ID:</strong> " . ($cdb_empleado_id ?: 'NULL') . "</p>";

// Registrar en el log
error_log("[DEBUG] Test.php ejecutado - Usuario: " . $current_user->display_name . " - Empleado ID: " . ($cdb_empleado_id ?: 'NULL'));
