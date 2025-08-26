<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Callback for the main CdB Form page.
 */
function cdb_form_admin_page() {
    if ( ! current_user_can( 'manage_cdb_forms' ) ) {
        return;
    }

    $intro = '';
    $readme_path = CDB_FORM_PATH . 'README.md';
    if ( file_exists( $readme_path ) ) {
        $readme_content = file_get_contents( $readme_path );
        if ( false !== $readme_content ) {
            $readme_content = preg_replace( '/^#.*\R+/', '', $readme_content );
            $parts         = preg_split( "/\R\R+/", trim( $readme_content ) );
            if ( ! empty( $parts[0] ) ) {
                $intro = wpautop( trim( $parts[0] ) );
            }
        }
    }

    echo '<div class="wrap"><h1>' . esc_html__( 'CdB Form', 'cdb-form' ) . '</h1>';
    if ( $intro ) {
        echo wp_kses_post( $intro );
    }
    echo '<ul>';
    echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=cdb-form-disenio-empleado' ) ) . '">' . esc_html__( 'Configuración Crear Empleado', 'cdb-form' ) . '</a> - ' . esc_html__( 'Formulario para crear o actualizar el perfil de empleado.', 'cdb-form' ) . '</li>';
    echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=cdb-form-config-mensajes' ) ) . '">' . esc_html__( 'Configuración de Mensajes y Avisos', 'cdb-form' ) . '</a> - ' . esc_html__( 'Personaliza los avisos, errores e instrucciones mostrados por los shortcodes.', 'cdb-form' ) . '</li>';
    echo '</ul></div>';
}

/**
 * Render admin page and handle form submission.
 */
function cdb_form_disenio_empleado_page() {
    if ( ! current_user_can( 'manage_cdb_forms' ) ) {
        return;
    }

    // Defaults
    $defaults = array(
        'background_color'          => '#fafafa',
        'border_color'              => '#ddd',
        'text_color'                => '#000000',
        'button_bg'                 => '#000000',
        'button_text_color'         => '#ffffff',
        'font_size'                 => 14,
        'padding'                   => 20,
        'field_spacing'             => 10,
        'success_message_color'     => '#008000',
        'error_message_color'       => '#FF0000',
        'container_background_color'=> '#ffffff',
        'margin_top'                => 0,
        'margin_right'              => 0,
        'margin_bottom'             => 0,
        'margin_left'               => 0,
        'alignment'                 => 'center',
    );

    // Handle save
    if ( isset( $_POST['cdb_form_disenio_empleado_nonce'] ) &&
         check_admin_referer( 'cdb_form_disenio_empleado_save', 'cdb_form_disenio_empleado_nonce' ) ) {

        $options = array(
            'background_color'          => sanitize_hex_color( $_POST['background_color'] ),
            'border_color'              => sanitize_hex_color( $_POST['border_color'] ),
            'text_color'                => sanitize_hex_color( $_POST['text_color'] ),
            'button_bg'                 => sanitize_hex_color( $_POST['button_bg'] ),
            'button_text_color'         => sanitize_hex_color( $_POST['button_text_color'] ),
            'font_size'                 => intval( $_POST['font_size'] ),
            'padding'                   => intval( $_POST['padding'] ),
            'field_spacing'             => intval( $_POST['field_spacing'] ),
            'container_background_color'=> sanitize_hex_color( $_POST['container_background_color'] ),
            'success_message_color'     => sanitize_hex_color( $_POST['success_message_color'] ),
            'error_message_color'       => sanitize_hex_color( $_POST['error_message_color'] ),
            'margin_top'                => intval( $_POST['margin_top'] ),
            'margin_right'              => intval( $_POST['margin_right'] ),
            'margin_bottom'             => intval( $_POST['margin_bottom'] ),
            'margin_left'               => intval( $_POST['margin_left'] ),
            'alignment'                 => sanitize_text_field( $_POST['alignment'] ),
        );

        update_option( 'cdb_form_disenio_empleado', $options );
        echo '<div class="updated"><p>' . esc_html__( 'Opciones guardadas.', 'cdb-form' ) . '</p></div>';
    }

    $values = wp_parse_args( get_option( 'cdb_form_disenio_empleado' ), $defaults );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configuración Crear Empleado', 'cdb-form' ); ?></h1>
        <p><?php esc_html_e( 'Configura los estilos del formulario de empleado mostrado en el frontend. Estos cambios no afectan a otros formularios.', 'cdb-form' ); ?></p>
        <form method="post">
            <?php wp_nonce_field( 'cdb_form_disenio_empleado_save', 'cdb_form_disenio_empleado_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="container_background_color"><?php esc_html_e( 'Color de fondo del contenedor', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="container_background_color" class="cdb-color-input" name="container_background_color" value="<?php echo esc_attr( $values['container_background_color'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="container_background_color_value" value="<?php echo esc_attr( $values['container_background_color'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="background_color"><?php esc_html_e( 'Color de fondo del formulario', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="background_color" class="cdb-color-input" name="background_color" value="<?php echo esc_attr( $values['background_color'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="background_color_value" value="<?php echo esc_attr( $values['background_color'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="border_color"><?php esc_html_e( 'Color del borde del formulario', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="border_color" class="cdb-color-input" name="border_color" value="<?php echo esc_attr( $values['border_color'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="border_color_value" value="<?php echo esc_attr( $values['border_color'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="text_color"><?php esc_html_e( 'Color de texto de campos y etiquetas', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="text_color" class="cdb-color-input" name="text_color" value="<?php echo esc_attr( $values['text_color'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="text_color_value" value="<?php echo esc_attr( $values['text_color'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="button_bg"><?php esc_html_e( 'Color de fondo del botón', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="button_bg" class="cdb-color-input" name="button_bg" value="<?php echo esc_attr( $values['button_bg'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="button_bg_value" value="<?php echo esc_attr( $values['button_bg'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="button_text_color"><?php esc_html_e( 'Color de texto del botón', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="button_text_color" class="cdb-color-input" name="button_text_color" value="<?php echo esc_attr( $values['button_text_color'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="button_text_color_value" value="<?php echo esc_attr( $values['button_text_color'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="font_size"><?php esc_html_e( 'Tamaño de fuente de campos y etiquetas (px)', 'cdb-form' ); ?></label></th>
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
                    <th scope="row"><label for="success_message_color"><?php esc_html_e( 'Color de mensajes de éxito', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="success_message_color" class="cdb-color-input" name="success_message_color" value="<?php echo esc_attr( $values['success_message_color'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="success_message_color_value" value="<?php echo esc_attr( $values['success_message_color'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="error_message_color"><?php esc_html_e( 'Color de mensajes de error', 'cdb-form' ); ?></label></th>
                    <td>
                        <input type="color" id="error_message_color" class="cdb-color-input" name="error_message_color" value="<?php echo esc_attr( $values['error_message_color'] ); ?>" />
                        <input type="text" class="cdb-color-value" id="error_message_color_value" value="<?php echo esc_attr( $values['error_message_color'] ); ?>" readonly />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Márgenes del contenedor (px)', 'cdb-form' ); ?></th>
                    <td>
                        <label><?php esc_html_e( 'Superior', 'cdb-form' ); ?> <input type="number" name="margin_top" value="<?php echo esc_attr( $values['margin_top'] ); ?>" class="small-text" /></label>
                        <label><?php esc_html_e( 'Derecho', 'cdb-form' ); ?> <input type="number" name="margin_right" value="<?php echo esc_attr( $values['margin_right'] ); ?>" class="small-text" /></label>
                        <label><?php esc_html_e( 'Inferior', 'cdb-form' ); ?> <input type="number" name="margin_bottom" value="<?php echo esc_attr( $values['margin_bottom'] ); ?>" class="small-text" /></label>
                        <label><?php esc_html_e( 'Izquierdo', 'cdb-form' ); ?> <input type="number" name="margin_left" value="<?php echo esc_attr( $values['margin_left'] ); ?>" class="small-text" /></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="alignment"><?php esc_html_e( 'Alineación del contenedor', 'cdb-form' ); ?></label></th>
                    <td>
                        <select name="alignment" id="alignment">
                            <option value="left" <?php selected( $values['alignment'], 'left' ); ?>><?php esc_html_e( 'Izquierda', 'cdb-form' ); ?></option>
                            <option value="center" <?php selected( $values['alignment'], 'center' ); ?>><?php esc_html_e( 'Centro', 'cdb-form' ); ?></option>
                            <option value="right" <?php selected( $values['alignment'], 'right' ); ?>><?php esc_html_e( 'Derecha', 'cdb-form' ); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(function($){
        $('.cdb-color-input').on('input change', function(){
            $('#' + this.id + '_value').val( $(this).val() );
        });
    });
    </script>
    <?php
}

