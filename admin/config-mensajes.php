<?php
// Evitar acceso directo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Submenú de configuración de mensajes y avisos del plugin CdB Form.
 *
 * Este panel centraliza la gestión de los textos y estilos usados
 * en la experiencia de usuario de CdB. Está preparado para añadidos
 * futuros mediante una estructura dinámica basada en arrays.
 */
function cdb_form_mensajes_admin_menu() {
    add_submenu_page(
        'cdb-form',
        __( 'Configuración de Mensajes y Avisos', 'cdb-form' ),
        __( 'Configuración de Mensajes y Avisos', 'cdb-form' ),
        'manage_options',
        'cdb-form-config-mensajes',
        'cdb_form_config_mensajes_page'
    );
}
add_action( 'admin_menu', 'cdb_form_mensajes_admin_menu' );

/**
 * Renderiza la página de opciones y guarda los valores.
 */
function cdb_form_config_mensajes_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Definición de los mensajes configurables.
    $mensajes = array(
        'bienvenida_usuario' => array(
            'text_option'  => 'cdb_mensaje_bienvenida_usuario',
            'color_option' => 'cdb_color_bienvenida_usuario',
            'label'        => __( 'Mensaje de Bienvenida (sin perfil)', 'cdb-form' ),
            'description'  => __( 'Este mensaje se muestra solo a usuarios que aún no han creado perfil de empleado', 'cdb-form' ),
        ),
        'bienvenida_gracias' => array(
            'text_option'  => 'cdb_mensaje_bienvenida_gracias',
            'color_option' => 'cdb_color_bienvenida_gracias',
            'label'        => __( 'Mensaje de Agradecimiento', 'cdb-form' ),
            'description'  => __( 'Texto opcional de agradecimiento mostrado en la bienvenida', 'cdb-form' ),
        ),
    );

    $tipos_color = array(
        'aviso'        => __( 'Aviso', 'cdb-form' ),
        'info'         => __( 'Info', 'cdb-form' ),
        'exito'        => __( 'Éxito', 'cdb-form' ),
        'motivacional' => __( 'Motivacional', 'cdb-form' ),
    );

    if ( isset( $_POST['cdb_form_config_mensajes_nonce'] ) &&
         check_admin_referer( 'cdb_form_config_mensajes_save', 'cdb_form_config_mensajes_nonce' ) ) {
        foreach ( $mensajes as $datos ) {
            if ( isset( $_POST[ $datos['text_option'] ] ) ) {
                update_option( $datos['text_option'], wp_kses_post( $_POST[ $datos['text_option'] ] ) );
            }
            if ( isset( $_POST[ $datos['color_option'] ] ) ) {
                update_option( $datos['color_option'], sanitize_text_field( $_POST[ $datos['color_option'] ] ) );
            }
        }
        echo '<div class="updated"><p>' . esc_html__( 'Opciones guardadas.', 'cdb-form' ) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configuración de Mensajes y Avisos', 'cdb-form' ); ?></h1>
        <p><?php esc_html_e( 'Este panel centraliza la gestión de mensajes/avisos de la experiencia de usuario CdB.', 'cdb-form' ); ?></p>
        <form method="post">
            <?php wp_nonce_field( 'cdb_form_config_mensajes_save', 'cdb_form_config_mensajes_nonce' ); ?>
            <table class="form-table" role="presentation">
                <?php foreach ( $mensajes as $datos ) :
                    $texto  = get_option( $datos['text_option'], '' );
                    $color  = get_option( $datos['color_option'], 'aviso' );
                    ?>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr( $datos['text_option'] ); ?>"><?php echo esc_html( $datos['label'] ); ?></label></th>
                        <td>
                            <textarea class="large-text" rows="3" id="<?php echo esc_attr( $datos['text_option'] ); ?>" name="<?php echo esc_attr( $datos['text_option'] ); ?>"><?php echo esc_textarea( $texto ); ?></textarea>
                            <p class="description"><?php echo esc_html( $datos['description'] ); ?></p>
                            <label for="<?php echo esc_attr( $datos['color_option'] ); ?>"><?php esc_html_e( 'Tipo/Color', 'cdb-form' ); ?></label>
                            <select id="<?php echo esc_attr( $datos['color_option'] ); ?>" name="<?php echo esc_attr( $datos['color_option'] ); ?>">
                                <?php foreach ( $tipos_color as $valor => $label ) : ?>
                                    <option value="<?php echo esc_attr( $valor ); ?>" <?php selected( $color, $valor ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
