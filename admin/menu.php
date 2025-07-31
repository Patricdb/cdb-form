<?php
// Avoid direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register admin menu for CdB Form settings.
 */
function cdb_form_register_admin_menu() {
    add_menu_page(
        __( 'CdB Form', 'cdb-form' ), // Page title
        __( 'CdB Form', 'cdb-form' ), // Menu title
        'manage_options',             // Capability
        'cdb_form',                   // Menu slug
        'cdb_form_settings_page',     // Callback
        'dashicons-feedback',         // Icon
        26                             // Position
    );
}
add_action( 'admin_menu', 'cdb_form_register_admin_menu' );

/**
 * Display settings page with tabs.
 */
function cdb_form_settings_page() {
    // Load existing settings
    $settings = get_option( 'cdb_form_settings', array(
        'experience_enabled'     => 0,
        'employee_enabled'       => 0,
        'experiencia_bg_color'   => '#FAF8EE',
        'experiencia_border_color' => '#cdb888',
        'empleado_bg_color'      => '#fafafa',
        'empleado_border_color'  => '#ddd',
    ) );

    // Handle form submission
    if ( isset( $_POST['cdb_form_save_settings'] ) ) {
        check_admin_referer( 'cdb_form_save_settings' );

        $settings['experience_enabled'] = isset( $_POST['experience_enabled'] ) ? 1 : 0;
        $settings['employee_enabled']   = isset( $_POST['employee_enabled'] ) ? 1 : 0;

        update_option( 'cdb_form_settings', $settings );
        echo '<div class="updated"><p>' . __( 'Ajustes guardados.', 'cdb-form' ) . '</p></div>';
    }

    if ( isset( $_POST['cdb_form_save_design'] ) ) {
        check_admin_referer( 'cdb_form_save_design' );

        $settings['experiencia_bg_color']   = sanitize_hex_color( $_POST['experiencia_bg_color'] );
        $settings['experiencia_border_color'] = sanitize_hex_color( $_POST['experiencia_border_color'] );
        $settings['empleado_bg_color']      = sanitize_hex_color( $_POST['empleado_bg_color'] );
        $settings['empleado_border_color']  = sanitize_hex_color( $_POST['empleado_border_color'] );

        update_option( 'cdb_form_settings', $settings );
        echo '<div class="updated"><p>' . __( 'Ajustes guardados.', 'cdb-form' ) . '</p></div>';
    }

    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
    ?>
    <div class="wrap cdb-form-admin-wrap">
        <h1><?php echo esc_html__( 'Configuración de CdB Form', 'cdb-form' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=cdb_form&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'General', 'cdb-form' ); ?>
            </a>
            <a href="?page=cdb_form&tab=access" class="nav-tab <?php echo $active_tab === 'access' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Acceso', 'cdb-form' ); ?>
            </a>
            <a href="?page=cdb_form&tab=design" class="nav-tab <?php echo $active_tab === 'design' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Diseño', 'cdb-form' ); ?>
            </a>
        </h2>
        <?php if ( $active_tab === 'general' ) : ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'cdb_form_save_settings' ); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="experience_enabled">
                                    <?php esc_html_e( 'Activar formularios de experiencia', 'cdb-form' ); ?>
                                </label>
                            </th>
                            <td>
                                <input name="experience_enabled" id="experience_enabled" type="checkbox" value="1" <?php checked( $settings['experience_enabled'], 1 ); ?> />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="employee_enabled">
                                    <?php esc_html_e( 'Activar formularios de empleado', 'cdb-form' ); ?>
                                </label>
                            </th>
                            <td>
                                <input name="employee_enabled" id="employee_enabled" type="checkbox" value="1" <?php checked( $settings['employee_enabled'], 1 ); ?> />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="cdb_form_save_settings" value="1" />
                <?php submit_button(); ?>
            </form>
        <?php elseif ( $active_tab === 'design' ) : ?>
            <form method="post" action="">
                <?php wp_nonce_field( 'cdb_form_save_design' ); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="experiencia_bg_color">
                                    <?php esc_html_e( 'Color de fondo del formulario de experiencia', 'cdb-form' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="color" name="experiencia_bg_color" id="experiencia_bg_color" value="<?php echo esc_attr( $settings['experiencia_bg_color'] ); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="experiencia_border_color">
                                    <?php esc_html_e( 'Color de borde del formulario de experiencia', 'cdb-form' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="color" name="experiencia_border_color" id="experiencia_border_color" value="<?php echo esc_attr( $settings['experiencia_border_color'] ); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="empleado_bg_color">
                                    <?php esc_html_e( 'Color de fondo del formulario de empleado', 'cdb-form' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="color" name="empleado_bg_color" id="empleado_bg_color" value="<?php echo esc_attr( $settings['empleado_bg_color'] ); ?>" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="empleado_border_color">
                                    <?php esc_html_e( 'Color de borde del formulario de empleado', 'cdb-form' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="color" name="empleado_border_color" id="empleado_border_color" value="<?php echo esc_attr( $settings['empleado_border_color'] ); ?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="cdb_form_save_design" value="1" />
                <?php submit_button(); ?>
            </form>
        <?php elseif ( $active_tab === 'access' ) : ?>
            <p>
                <?php esc_html_e( 'Actualmente el único rol permitido para usar el formulario es "Administrador".', 'cdb-form' ); ?>
            </p>
            <p><em><?php esc_html_e( 'La edición de permisos estará disponible próximamente.', 'cdb-form' ); ?></em></p>
        <?php endif; ?>
    </div>
    <?php
}
