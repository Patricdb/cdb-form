<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add main admin menu and submenus.
 */
function cdb_form_admin_menu() {
    add_menu_page(
        __( 'CdB Form', 'cdb-form' ),
        __( 'CdB Form', 'cdb-form' ),
        'manage_options',
        'cdb-form',
        'cdb_form_admin_page',
        'dashicons-forms'
    );

    add_submenu_page(
        'cdb-form',
        __( 'Dise\xC3\xB1o Crear Empleado', 'cdb-form' ),
        __( 'Dise\xC3\xB1o Crear Empleado', 'cdb-form' ),
        'manage_options',
        'cdb-form-disenio-empleado',
        'cdb_form_disenio_empleado_page'
    );
}
add_action( 'admin_menu', 'cdb_form_admin_menu' );

/**
 * Callback for the main CdB Form page.
 */
function cdb_form_admin_page() {
    echo '<div class="wrap"><h1>' . esc_html__( 'CdB Form', 'cdb-form' ) . '</h1></div>';
}

/**
 * Render admin page and handle form submission.
 */
function cdb_form_disenio_empleado_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Defaults
    $defaults = array(
        'background_color'    => '#fafafa',
        'border_color'        => '#ddd',
        'text_color'          => '#000000',
        'button_bg'           => '#000000',
        'button_text_color'   => '#ffffff',
        'font_size'           => 14,
        'padding'             => 20,
        'field_spacing'       => 10,
        'message_color'       => '#008000',
        'container_background_color' => '#ffffff',
    );

    // Handle save
    if ( isset( $_POST['cdb_form_disenio_empleado_nonce'] ) &&
         check_admin_referer( 'cdb_form_disenio_empleado_save', 'cdb_form_disenio_empleado_nonce' ) ) {

        $options = array(
            'background_color'  => sanitize_hex_color( $_POST['background_color'] ),
            'border_color'      => sanitize_hex_color( $_POST['border_color'] ),
            'text_color'        => sanitize_hex_color( $_POST['text_color'] ),
            'button_bg'         => sanitize_hex_color( $_POST['button_bg'] ),
            'button_text_color' => sanitize_hex_color( $_POST['button_text_color'] ),
            'font_size'         => intval( $_POST['font_size'] ),
            'padding'           => intval( $_POST['padding'] ),
            'field_spacing'     => intval( $_POST['field_spacing'] ),
            'container_background_color' => sanitize_hex_color( $_POST['container_background_color'] ),
            'message_color'     => sanitize_hex_color( $_POST['message_color'] ),
        );

        update_option( 'cdb_form_disenio_empleado', $options );
        echo '<div class="updated"><p>' . esc_html__( 'Opciones guardadas.', 'cdb-form' ) . '</p></div>';
    }

    $values = wp_parse_args( get_option( 'cdb_form_disenio_empleado' ), $defaults );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Dise\xC3\xB1o Crear Empleado', 'cdb-form' ); ?></h1>
        <p><?php esc_html_e( 'Configura los estilos del formulario de empleado mostrado en el frontend. Estos cambios no afectan a otros formularios.', 'cdb-form' ); ?></p>
        <form method="post">
            <?php wp_nonce_field( 'cdb_form_disenio_empleado_save', 'cdb_form_disenio_empleado_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="container_background_color"><?php esc_html_e( 'Color de fondo del contenedor', 'cdb-form' ); ?></label></th>
                    <td><input type="text" id="container_background_color" class="cdb-color-field" name="container_background_color" value="<?php echo esc_attr( $values['container_background_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="background_color"><?php esc_html_e( 'Color de fondo del formulario', 'cdb-form' ); ?></label></th>
                    <td><input type="text" id="background_color" class="cdb-color-field" name="background_color" value="<?php echo esc_attr( $values['background_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="border_color"><?php esc_html_e( 'Color del borde del formulario', 'cdb-form' ); ?></label></th>
                    <td><input type="text" id="border_color" class="cdb-color-field" name="border_color" value="<?php echo esc_attr( $values['border_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="text_color"><?php esc_html_e( 'Color de texto de campos y etiquetas', 'cdb-form' ); ?></label></th>
                    <td><input type="text" id="text_color" class="cdb-color-field" name="text_color" value="<?php echo esc_attr( $values['text_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="button_bg"><?php esc_html_e( 'Color de fondo del bot\xC3\xB3n', 'cdb-form' ); ?></label></th>
                    <td><input type="text" id="button_bg" class="cdb-color-field" name="button_bg" value="<?php echo esc_attr( $values['button_bg'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="button_text_color"><?php esc_html_e( 'Color de texto del bot\xC3\xB3n', 'cdb-form' ); ?></label></th>
                    <td><input type="text" id="button_text_color" class="cdb-color-field" name="button_text_color" value="<?php echo esc_attr( $values['button_text_color'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="font_size"><?php esc_html_e( 'Tama\xC3\xB1o de fuente de campos y etiquetas (px)', 'cdb-form' ); ?></label></th>
                    <td><input type="number" id="font_size" name="font_size" value="<?php echo esc_attr( $values['font_size'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="padding"><?php esc_html_e( 'Padding del contenedor principal (px)', 'cdb-form' ); ?></label></th>
                    <td><input type="number" id="padding" name="padding" value="<?php echo esc_attr( $values['padding'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="field_spacing"><?php esc_html_e( 'Espaciado vertical entre campos (px)', 'cdb-form' ); ?></label></th>
                    <td><input type="number" id="field_spacing" name="field_spacing" value="<?php echo esc_attr( $values['field_spacing'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><label for="message_color"><?php esc_html_e( 'Color de mensajes de \xC3\xA9xito y error', 'cdb-form' ); ?></label></th>
                    <td><input type="text" id="message_color" class="cdb-color-field" name="message_color" value="<?php echo esc_attr( $values['message_color'] ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(function($){
        $('.cdb-color-field').wpColorPicker();
    });
    </script>
    <?php
}

