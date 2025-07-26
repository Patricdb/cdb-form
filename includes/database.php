<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Función (legado) para crear la tabla cdb_posiciones
 * Se deja por compatibilidad, pero no se llama en cdb_form_create_tables().
 */
function cdb_crear_tabla_posiciones() {
    global $wpdb;
    $charset_collate    = $wpdb->get_charset_collate();
    $tabla_posiciones   = $wpdb->prefix . 'cdb_posiciones';

    $sql = "CREATE TABLE IF NOT EXISTS $tabla_posiciones (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL UNIQUE
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Insertar las 15 posiciones si no existen (legado)
    $posiciones = [
        "Gerente", "Jefe de Sala", "Jefe de Cocina", "Jefe de Rango", "Cocinero",
        "Barra", "Coctelería", "Camarero", "Runner", "Hostess", "Ayudante de Hostess",
        "Ayudante de Cocina", "Ayudante de Camarero", "Office de Cocina", "Office de Barra"
    ];

    foreach ($posiciones as $posicion) {
        $wpdb->query(
            $wpdb->prepare(
                "INSERT IGNORE INTO $tabla_posiciones (nombre) VALUES (%s)",
                $posicion
            )
        );
    }
}

/**
 * Crear la tabla `cdb_experiencia`, donde `posicion_id` hace referencia a wp_posts(ID).
 */
function cdb_crear_tabla_experiencia() {
    global $wpdb;
    $charset_collate      = $wpdb->get_charset_collate();
    $tabla_experiencia    = $wpdb->prefix . 'cdb_experiencia';

    // Creamos la tabla `cdb_experiencia` con FOREIGN KEY apuntando a wp_posts
    $sql_experiencia = "CREATE TABLE IF NOT EXISTS $tabla_experiencia (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        empleado_id BIGINT(20) UNSIGNED NOT NULL,
        bar_id BIGINT(20) UNSIGNED NOT NULL,
        posicion_id BIGINT(20) UNSIGNED NOT NULL,
        anio INT NOT NULL,
        equipo_id BIGINT(20) UNSIGNED DEFAULT NULL,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (empleado_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
        FOREIGN KEY (bar_id)      REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
        FOREIGN KEY (posicion_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE,
        FOREIGN KEY (equipo_id)   REFERENCES {$wpdb->prefix}posts(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_experiencia);
    error_log("Intentando crear tabla wp_cdb_experiencia con dbDelta().");
}

/**
 * Función de activación del plugin que crea las tablas necesarias.
 */
function cdb_form_create_tables() {
    // Ya no llamamos a cdb_crear_tabla_posiciones() 
    // porque las posiciones se gestionan como CPT.
    // Si deseas mantener la tabla antigua, descomenta la siguiente línea:
    // cdb_crear_tabla_posiciones();

    cdb_crear_tabla_experiencia();
}
