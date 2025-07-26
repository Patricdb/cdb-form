<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Función para agregar capacidades personalizadas
function cdb_form_add_capabilities() {
    $roles = array( 'administrator', 'editor', 'author' );

    foreach ( $roles as $role_name ) {
        $role = get_role( $role_name );

        if ( $role ) {
            $role->add_cap( 'manage_cdb_forms' );
            $role->add_cap( 'edit_cdb_empleados' );
            $role->add_cap( 'edit_cdb_bares' );
        }
    }
}

// Función para eliminar capacidades al desactivar el plugin
function cdb_form_remove_capabilities() {
    $roles = array( 'administrator', 'editor', 'author' );

    foreach ( $roles as $role_name ) {
        $role = get_role( $role_name );

        if ( $role ) {
            $role->remove_cap( 'manage_cdb_forms' );
            $role->remove_cap( 'edit_cdb_empleados' );
            $role->remove_cap( 'edit_cdb_bares' );
        }
    }
}

// Agregar capacidades en la activación del plugin
register_activation_hook( CDB_FORM_PATH . 'cdb-form.php', 'cdb_form_add_capabilities' );

// Eliminar capacidades en la desactivación del plugin
register_deactivation_hook( CDB_FORM_PATH . 'cdb-form.php', 'cdb_form_remove_capabilities' );
