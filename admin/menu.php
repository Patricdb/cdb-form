<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register main plugin menu and all submenus.
 */
function cdb_form_register_menus() {
    add_menu_page(
        __( 'CdB Form', 'cdb-form' ),
        __( 'CdB Form', 'cdb-form' ),
        'manage_cdb_forms',
        'cdb-form',
        'cdb_form_admin_page',
        'dashicons-forms',
        26
    );

    // Remove duplicate link to top-level page.
    remove_submenu_page( 'cdb-form', 'cdb-form' );

    add_submenu_page(
        'cdb-form',
        __( 'CdB Form', 'cdb-form' ),
        __( 'CdB Form', 'cdb-form' ),
        'manage_cdb_forms',
        'cdb-form',
        'cdb_form_admin_page'
    );

    add_submenu_page(
        'cdb-form',
        __( 'Configuración Crear Empleado', 'cdb-form' ),
        __( 'Diseño del formulario', 'cdb-form' ),
        'manage_cdb_forms',
        'cdb-form-disenio-empleado',
        'cdb_form_disenio_empleado_page'
    );

    add_submenu_page(
        'cdb-form',
        __( 'Configuración de Mensajes y Avisos', 'cdb-form' ),
        __( 'Mensajes y avisos', 'cdb-form' ),
        'manage_cdb_forms',
        'cdb-form-config-mensajes',
        'cdb_form_config_mensajes_page'
    );
}
add_action( 'admin_menu', 'cdb_form_register_menus' );

