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

    // Definición de los mensajes configurables en el orden solicitado.
    $mensajes = array(
        'saludo_usuario' => array(
            'text_option'  => 'cdb_mensaje_saludo_usuario',
            'color_option' => 'cdb_color_saludo_usuario',
            'label'        => __( 'Saludo al Usuario', 'cdb-form' ),
            'description'  => __( 'Texto de saludo inicial. Usa %s para incluir el nombre del usuario.', 'cdb-form' ),
        ),
        'bienvenida_general' => array(
            'text_option'  => 'cdb_mensaje_bienvenida',
            'color_option' => 'cdb_color_bienvenida',
            'label'        => __( 'Mensaje de Bienvenida', 'cdb-form' ),
            'description'  => __( 'Texto mostrado tras el saludo inicial.', 'cdb-form' ),
        ),
        'empleado_sin_perfil' => array(
            'text_option'  => 'cdb_mensaje_bienvenida_usuario',
            'color_option' => 'cdb_color_bienvenida_usuario',
            'label'        => __( 'Mensaje para Empleado sin perfil', 'cdb-form' ),
            'description'  => __( 'Se muestra a empleados que aún no han creado su perfil.', 'cdb-form' ),
        ),
        'empleado_sin_experiencia' => array(
            'text_option'  => 'cdb_mensaje_empleado_sin_experiencia',
            'color_option' => 'cdb_color_empleado_sin_experiencia',
            'label'        => __( 'Mensaje para Empleado sin experiencia', 'cdb-form' ),
            'description'  => __( 'Se muestra a empleados con perfil pero sin experiencia registrada.', 'cdb-form' ),
        ),
    );

    // Tipos/color disponibles
    $tipos_color = cdb_form_get_tipos_color();

    $mensaje_guardado = '';
    $tipo_mensaje     = 'exito';

    if (
        isset( $_POST['cdb_form_config_mensajes_nonce'] ) &&
        check_admin_referer( 'cdb_form_config_mensajes_save', 'cdb_form_config_mensajes_nonce' )
    ) {
        // Guardar textos y tipo/color de cada mensaje
        foreach ( $mensajes as $datos ) {
            if ( isset( $_POST[ $datos['text_option'] ] ) ) {
                update_option( $datos['text_option'], wp_kses_post( $_POST[ $datos['text_option'] ] ) );
            }
            if ( isset( $_POST[ $datos['color_option'] ] ) ) {
                update_option( $datos['color_option'], sanitize_key( $_POST[ $datos['color_option'] ] ) );
            }
        }

        // Guardar tipos/color
        $tipos_nuevos   = array();
        $nombres        = array();
        $duplicado      = false;
        if ( isset( $_POST['tipos_color'] ) && is_array( $_POST['tipos_color'] ) ) {
            foreach ( $_POST['tipos_color'] as $slug => $datos ) {
                if ( isset( $datos['delete'] ) && '1' === $datos['delete'] ) {
                    continue; // saltar elementos marcados para borrar
                }
                $nombre = sanitize_text_field( $datos['name'] ?? '' );
                if ( in_array( $nombre, $nombres, true ) ) {
                    $duplicado = true;
                    break;
                }
                $nombres[] = $nombre;
                $slug_sanit = sanitize_key( $slug );
                $tipos_nuevos[ $slug_sanit ] = array(
                    'name'  => $nombre,
                    'class' => sanitize_html_class( $datos['class'] ?? '' ),
                    'color' => sanitize_hex_color( $datos['color'] ?? '' ),
                    'text'  => sanitize_hex_color( $datos['text'] ?? '' ),
                );
            }
            if ( ! $duplicado ) {
                update_option( 'cdb_form_tipos_color', $tipos_nuevos );
                $tipos_color = cdb_form_get_tipos_color(); // refrescar
            }
        }

        if ( $duplicado ) {
            $mensaje_guardado = __( 'No se pudo guardar: nombres de tipo/color duplicados.', 'cdb-form' );
            $tipo_mensaje     = 'aviso';
        } else {
            $mensaje_guardado = __( 'Opciones guardadas.', 'cdb-form' );
        }
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configuración de Mensajes y Avisos', 'cdb-form' ); ?></h1>
        <p><?php esc_html_e( 'Este panel centraliza la gestión de mensajes/avisos de la experiencia de usuario CdB.', 'cdb-form' ); ?></p>
        <?php if ( $mensaje_guardado ) :
            $clase_notice = cdb_form_get_tipo_color_class( $tipo_mensaje );
            $bg_notice    = $tipos_color[ $tipo_mensaje ]['color'] ?? '#000';
            $text_notice  = $tipos_color[ $tipo_mensaje ]['text'] ?? cdb_form_get_contrasting_text_color( $bg_notice );
            ?>
            <div class="cdb-aviso <?php echo esc_attr( $clase_notice ); ?>" style="border-left-color: <?php echo esc_attr( $bg_notice ); ?>; background-color: <?php echo esc_attr( $bg_notice ); ?>; color: <?php echo esc_attr( $text_notice ); ?>;">
                <p><?php echo esc_html( $mensaje_guardado ); ?></p>
            </div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field( 'cdb_form_config_mensajes_save', 'cdb_form_config_mensajes_nonce' ); ?>

            <?php foreach ( $mensajes as $id => $datos ) :
                $texto      = get_option( $datos['text_option'], '' );
                $tipo       = get_option( $datos['color_option'], 'aviso' );
                $datos_tipo = $tipos_color[ $tipo ] ?? array();
                $clase      = $datos_tipo['class'] ?? '';
                $color_hex  = $datos_tipo['color'] ?? '#000';
                $text_hex   = $datos_tipo['text'] ?? cdb_form_get_contrasting_text_color( $color_hex );
                ?>
                <div class="cdb-config-mensaje" id="mensaje-<?php echo esc_attr( $id ); ?>">
                    <strong><?php echo esc_html( $datos['label'] ); ?></strong>
                    <div class="cdb-mensaje-preview <?php echo esc_attr( $clase ); ?>" style="border-left-color: <?php echo esc_attr( $color_hex ); ?>; background-color: <?php echo esc_attr( $color_hex ); ?>; color: <?php echo esc_attr( $text_hex ); ?>;">
                        <?php echo esc_html( $texto ); ?>
                    </div>
                    <button type="button" class="button cdb-edit-mensaje"><?php esc_html_e( 'Editar', 'cdb-form' ); ?></button>
                    <div class="cdb-mensaje-edicion" style="display:none;">
                        <textarea class="large-text" rows="3" name="<?php echo esc_attr( $datos['text_option'] ); ?>"><?php echo esc_textarea( $texto ); ?></textarea>
                        <p class="description"><?php echo esc_html( $datos['description'] ); ?></p>
                        <label><?php esc_html_e( 'Tipo/Color', 'cdb-form' ); ?></label>
                        <select name="<?php echo esc_attr( $datos['color_option'] ); ?>">
                            <?php foreach ( $tipos_color as $slug => $info ) : ?>
                                <option value="<?php echo esc_attr( $slug ); ?>" data-color="<?php echo esc_attr( $info['color'] ); ?>" data-text="<?php echo esc_attr( $info['text'] ); ?>" data-class="<?php echo esc_attr( $info['class'] ); ?>" style="color: <?php echo esc_attr( $info['color'] ); ?>;" <?php selected( $tipo, $slug ); ?>>&#11044; <?php echo esc_html( $info['name'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Clase CSS:', 'cdb-form' ); ?> <code class="cdb-clase-css"><?php echo esc_html( $clase ); ?></code></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <h2><?php esc_html_e( 'Tipos/Colores', 'cdb-form' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Selecciona colores de fondo y texto con suficiente contraste para garantizar la legibilidad.', 'cdb-form' ); ?></p>
            <div id="cdb-tipos-color">
                <?php foreach ( $tipos_color as $slug => $info ) : ?>
                    <div class="cdb-tipo-color-row">
                        <span class="cdb-color-swatch" style="background-color: <?php echo esc_attr( $info['color'] ); ?>"></span>
                        <input type="text" name="tipos_color[<?php echo esc_attr( $slug ); ?>][name]" value="<?php echo esc_attr( $info['name'] ); ?>" />
                        <input type="text" name="tipos_color[<?php echo esc_attr( $slug ); ?>][class]" value="<?php echo esc_attr( $info['class'] ); ?>" />
                        <input type="color" name="tipos_color[<?php echo esc_attr( $slug ); ?>][color]" value="<?php echo esc_attr( $info['color'] ); ?>" />
                        <input type="color" name="tipos_color[<?php echo esc_attr( $slug ); ?>][text]" value="<?php echo esc_attr( $info['text'] ); ?>" />
                        <label><input type="checkbox" name="tipos_color[<?php echo esc_attr( $slug ); ?>][delete]" value="1" /><?php esc_html_e( 'Eliminar', 'cdb-form' ); ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <p><button type="button" class="button" id="cdb-add-tipo-color"><?php esc_html_e( 'Añadir tipo/color', 'cdb-form' ); ?></button></p>

            <?php submit_button( __( 'Guardar cambios', 'cdb-form' ) ); ?>
        </form>
    </div>
    <?php
}
