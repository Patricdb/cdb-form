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

/**
 * Renderiza la página de opciones y guarda los valores.
 */
function cdb_form_config_mensajes_page() {
    if ( ! current_user_can( 'manage_cdb_forms' ) ) {
        return;
    }

    // Definición de los mensajes configurables.
    $mensajes = array(
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
        'login_requerido' => array(
            'text_option'  => 'cdb_mensaje_login_requerido',
            'color_option' => 'cdb_color_login_requerido',
            'label'        => __( 'Acceso restringido sin inicio de sesión', 'cdb-form' ),
            'description'  => __( 'Se muestra a usuarios no autenticados.', 'cdb-form' ),
        ),
        'empleado_no_encontrado' => array(
            'text_option'  => 'cdb_mensaje_empleado_no_encontrado',
            'color_option' => 'cdb_color_empleado_no_encontrado',
            'label'        => __( 'Empleado no encontrado', 'cdb-form' ),
            'description'  => __( 'Aparece cuando no existe un empleado asociado al usuario.', 'cdb-form' ),
        ),
        'puntuacion_no_disponible' => array(
            'text_option'  => 'cdb_mensaje_puntuacion_no_disponible',
            'color_option' => 'cdb_color_puntuacion_no_disponible',
            'label'        => __( 'Puntuación no disponible', 'cdb-form' ),
            'description'  => __( 'Se muestra cuando no hay puntuación gráfica registrada.', 'cdb-form' ),
        ),
        'sin_empleados' => array(
            'text_option'  => 'cdb_mensaje_sin_empleados',
            'color_option' => 'cdb_color_sin_empleados',
            'label'        => __( 'Listado de empleados vacío', 'cdb-form' ),
            'description'  => __( 'Se muestra cuando un listado de empleados no tiene resultados.', 'cdb-form' ),
        ),
        'busqueda_sin_bares' => array(
            'text_option'  => 'cdb_mensaje_busqueda_sin_bares',
            'color_option' => 'cdb_color_busqueda_sin_bares',
            'label'        => __( 'Búsqueda de bares sin resultados', 'cdb-form' ),
            'description'  => __( 'Se muestra cuando la búsqueda de bares no devuelve resultados.', 'cdb-form' ),
        ),
        'busqueda_sin_empleados' => array(
            'text_option'  => 'cdb_mensaje_busqueda_sin_empleados',
            'color_option' => 'cdb_color_busqueda_sin_empleados',
            'label'        => __( 'Búsqueda de empleados sin resultados', 'cdb-form' ),
            'description'  => __( 'Se muestra cuando la búsqueda de empleados no devuelve resultados.', 'cdb-form' ),
        ),
        'experiencia_sin_perfil' => array(
            'text_option'  => 'cdb_mensaje_experiencia_sin_perfil',
            'color_option' => 'cdb_color_experiencia_sin_perfil',
            'label'        => __( 'Experiencia sin perfil', 'cdb-form' ),
            'description'  => __( 'Aparece en el formulario de experiencia cuando falta el perfil de empleado.', 'cdb-form' ),
        ),
        'posicion_no_valida' => array(
            'text_option'  => 'cdb_mensaje_posicion_no_valida',
            'color_option' => 'cdb_color_posicion_no_valida',
            'label'        => __( 'Posición no válida', 'cdb-form' ),
            'description'  => __( 'Se muestra cuando falta una posición al listar empleados por posición.', 'cdb-form' ),
        ),
        'bar_sin_registro' => array(
            'text_option'  => 'cdb_mensaje_bar_sin_registro',
            'color_option' => 'cdb_color_bar_sin_registro',
            'label'        => __( 'Usuario sin bar registrado', 'cdb-form' ),
            'description'  => __( 'Se muestra cuando el usuario intenta actualizar un bar sin tener uno asignado.', 'cdb-form' ),
        ),
        'sin_permiso' => array(
            'text_option'  => 'cdb_mensaje_sin_permiso',
            'color_option' => 'cdb_color_sin_permiso',
            'label'        => __( 'Acceso sin permisos', 'cdb-form' ),
            'description'  => __( 'Se muestra cuando el usuario no tiene permisos para acceder a la sección.', 'cdb-form' ),
        ),
        'disponibilidad_sin_perfil' => array(
            'text_option'  => 'cdb_mensaje_disponibilidad_sin_perfil',
            'color_option' => 'cdb_color_disponibilidad_sin_perfil',
            'label'        => __( 'Disponibilidad sin perfil', 'cdb-form' ),
            'description'  => __( 'Aparece al actualizar disponibilidad sin tener perfil de empleado.', 'cdb-form' ),
        ),
    );

    $screen = get_current_screen();
    if ( $screen ) {
        $help_content  = '<p>' . esc_html__( 'Mensajes configurables y su uso:', 'cdb-form' ) . '</p><ul>';
        foreach ( $mensajes as $datos ) {
            $help_content .= '<li><strong>' . esc_html( $datos['label'] ) . ':</strong> ' . esc_html( $datos['description'] ) . '</li>';
        }
        $help_content .= '</ul><p>' . esc_html__( 'Cada bloque permite definir texto principal, secundario, color y visibilidad.', 'cdb-form' ) . '</p>';
        $help_content .= '<p><a href="' . esc_url( CDB_FORM_URL . 'docs/' ) . '" target="_blank">' . esc_html__( 'Documentación completa', 'cdb-form' ) . '</a></p>';
        $screen->add_help_tab(
            array(
                'id'      => 'cdb_form_config_mensajes_help',
                'title'   => __( 'Ayuda', 'cdb-form' ),
                'content' => $help_content,
            )
        );
    }

    global $cdb_form_defaults;
    $placeholder_map = array(
        'cdb_mensaje_puntuacion_no_disponible' => 'cdb_aviso_sin_puntuacion',
        'cdb_mensaje_empleado_no_encontrado'   => 'cdb_empleado_no_encontrado',
        'cdb_mensaje_experiencia_sin_perfil'   => 'cdb_experiencia_sin_perfil',
        'cdb_mensaje_busqueda_sin_bares'       => 'cdb_bares_sin_resultados',
        'cdb_mensaje_sin_empleados'            => 'cdb_empleados_vacio',
        'cdb_mensaje_busqueda_sin_empleados'   => 'cdb_empleados_sin_resultados',
        'cdb_mensaje_login_requerido'          => 'cdb_acceso_sin_login',
        'cdb_mensaje_sin_permiso'              => 'cdb_acceso_sin_permisos',
        'cdb_mensaje_bienvenida'               => 'cdb_mensaje_bienvenida',
        'cdb_mensaje_bienvenida_usuario'       => 'cdb_mensaje_bienvenida_usuario',
        'cdb_mensaje_empleado_sin_experiencia' => 'cdb_mensaje_empleado_sin_experiencia',
        'cdb_mensaje_posicion_no_valida'       => 'cdb_mensaje_posicion_no_valida',
        'cdb_mensaje_bar_sin_registro'         => 'cdb_mensaje_bar_sin_registro',
        'cdb_mensaje_disponibilidad_sin_perfil' => 'cdb_mensaje_disponibilidad_sin_perfil',
    );

    // Ejemplos de futuros mensajes que podrían añadirse:
    // 'exito_guardado' => array(
    //     'text_option'  => 'cdb_mensaje_exito_guardado',
    //     'color_option' => 'cdb_color_exito_guardado',
    //     'label'        => __( 'Acción completada con éxito', 'cdb-form' ),
    //     'description'  => __( 'Útil para confirmar guardados o actualizaciones.', 'cdb-form' ),
    // ),
    // 'listado_vacio_motivacional' => array(
    //     'text_option'  => 'cdb_mensaje_listado_vacio',
    //     'color_option' => 'cdb_color_listado_vacio',
    //     'label'        => __( 'Listado vacío motivacional', 'cdb-form' ),
    //     'description'  => __( 'Mensajes positivos cuando un listado no tiene entradas.', 'cdb-form' ),
    // ),

    // Tipos/color disponibles
    $tipos_color = cdb_form_get_tipos_color();

    $mensaje_guardado = '';
    $tipo_mensaje     = 'exito';

    if (
        isset( $_POST['cdb_form_config_mensajes_nonce'] ) &&
        check_admin_referer( 'cdb_form_config_mensajes_save', 'cdb_form_config_mensajes_nonce' )
    ) {
        // Guardar textos (principal y secundario) y tipo/color de cada mensaje
        foreach ( $mensajes as $datos ) {
            $sec_opt  = $datos['text_option'] . '_secundaria';
            $show_opt = $datos['text_option'] . '_mostrar';

            if ( isset( $_POST[ $datos['text_option'] ] ) ) {
                update_option( $datos['text_option'], wp_kses_post( $_POST[ $datos['text_option'] ] ) );
            }
            if ( isset( $_POST[ $sec_opt ] ) ) {
                update_option( $sec_opt, wp_kses_post( $_POST[ $sec_opt ] ) );
            }
            if ( isset( $_POST[ $datos['color_option'] ] ) ) {
                update_option( $datos['color_option'], sanitize_key( $_POST[ $datos['color_option'] ] ) );
            }

            // Guardar estado de visibilidad (1 = mostrar, 0 = ocultar).
            $mostrar = isset( $_POST[ $show_opt ] ) ? '1' : '0';
            update_option( $show_opt, $mostrar );
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
                $bg         = sanitize_hex_color( $datos['bg'] ?? '' );
                $text       = sanitize_hex_color( $datos['text'] ?? '' );
                $bcolor     = sanitize_hex_color( $datos['border_color'] ?? $bg );
                $bwidth     = cdb_form_normalize_border_value( $datos['border_width'] ?? '0px', '0px' );
                $bradius    = cdb_form_normalize_border_value( $datos['border_radius'] ?? '4px', '4px' );
                $tipos_nuevos[ $slug_sanit ] = array(
                    'name'         => $nombre,
                    'class'        => sanitize_html_class( $datos['class'] ?? '' ),
                    'bg'           => $bg,
                    'text'         => $text,
                    'border_color' => $bcolor,
                    'border_width' => $bwidth,
                    'border_radius'=> $bradius,
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
    $current_page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
    $disenio_url  = admin_url( 'admin.php?page=cdb-form-disenio-empleado' );
    $mensajes_url = admin_url( 'admin.php?page=cdb-form-config-mensajes' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configuración de Mensajes y Avisos', 'cdb-form' ); ?></h1>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url( $disenio_url ); ?>" class="nav-tab<?php echo ( 'cdb-form-disenio-empleado' === $current_page ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Diseño del formulario', 'cdb-form' ); ?></a>
            <a href="<?php echo esc_url( $mensajes_url ); ?>" class="nav-tab<?php echo ( 'cdb-form-config-mensajes' === $current_page ) ? ' nav-tab-active' : ''; ?>"><?php esc_html_e( 'Mensajes y avisos', 'cdb-form' ); ?></a>
        </h2>
        <p><?php esc_html_e( 'Este panel centraliza la gestión de mensajes/avisos de la experiencia de usuario CdB.', 'cdb-form' ); ?></p>
        <div class="notice notice-info"><p><?php esc_html_e( 'Si dejas un campo vacío se mostrará el texto por defecto', 'cdb-form' ); ?></p></div>
        <?php if ( $mensaje_guardado ) :
            $clase_notice = cdb_form_get_tipo_color_class( $tipo_mensaje );
            $bg_notice    = $tipos_color[ $tipo_mensaje ]['bg'] ?? '#000';
            $text_notice  = $tipos_color[ $tipo_mensaje ]['text'] ?? cdb_form_get_contrasting_text_color( $bg_notice );
            $bcolor_n     = $tipos_color[ $tipo_mensaje ]['border_color'] ?? $bg_notice;
            $bwidth_n     = $tipos_color[ $tipo_mensaje ]['border_width'] ?? '0px';
            $bradius_n    = $tipos_color[ $tipo_mensaje ]['border_radius'] ?? '4px';
            $style_notice = sprintf( 'background-color:%1$s;color:%2$s;border:%3$s solid %4$s;border-radius:%5$s;', esc_attr( $bg_notice ), esc_attr( $text_notice ), esc_attr( $bwidth_n ), esc_attr( $bcolor_n ), esc_attr( $bradius_n ) );
            if ( preg_match( '/^0(?:px|rem|em|%)?$/', $bwidth_n ) ) {
                $style_notice .= 'border-left:4px solid ' . esc_attr( $bcolor_n ) . ';';
            }
            ?>
            <div class="cdb-aviso <?php echo esc_attr( $clase_notice ); ?>" style="<?php echo esc_attr( $style_notice ); ?>">
                <strong class="cdb-mensaje-destacado"><?php echo esc_html( $mensaje_guardado ); ?></strong>
            </div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field( 'cdb_form_config_mensajes_save', 'cdb_form_config_mensajes_nonce' ); ?>

            <?php foreach ( $mensajes as $id => $datos ) :
            $texto = cdb_form_get_option_compat(
                array(
                    $datos['text_option'],
                    $datos['text_option'] . '_destacado',
                    $datos['text_option'] . '_principal',
                    $datos['text_option'] . '_mensaje_destacado',
                    $datos['text_option'] . '_mensaje_principal',
                    $datos['text_option'] . '_frase_destacada',
                    $datos['text_option'] . '_frase_principal',
                    $datos['text_option'] . '_featured',
                    $datos['text_option'] . '_primary',
                    $datos['text_option'] . '_highlight',
                ),
                ''
            );
            $placeholder = '';
            if ( isset( $placeholder_map[ $datos['text_option'] ] ) && isset( $cdb_form_defaults[ $placeholder_map[ $datos['text_option'] ] ] ) ) {
                $placeholder = $cdb_form_defaults[ $placeholder_map[ $datos['text_option'] ] ];
            }
                $sec_opt    = $datos['text_option'] . '_secundaria';
                $secundario = cdb_form_get_option_compat(
                    array(
                        $sec_opt,
                        $datos['text_option'] . '_secundario',
                        $datos['text_option'] . '_mensaje_secundario',
                        $datos['text_option'] . '_mensaje_secundaria',
                        $datos['text_option'] . '_frase_secundaria',
                        $datos['text_option'] . '_frase_secundario',
                        $datos['text_option'] . '_secondary',
                    ),
                    ''
                );
                $show_opt  = $datos['text_option'] . '_mostrar';
                $mostrar   = get_option( $show_opt, '1' );
                $clave_i18n      = $placeholder_map[ $datos['text_option'] ] ?? $datos['text_option'];
                $traduccion_i18n = cdb_form_get_mensaje_i18n( $clave_i18n );
                $tipo       = get_option( $datos['color_option'], 'aviso' );
                $datos_tipo = $tipos_color[ $tipo ] ?? array();
                $clase      = $datos_tipo['class'] ?? '';
                $color_hex  = $datos_tipo['bg'] ?? '#000';
                $text_hex   = $datos_tipo['text'] ?? cdb_form_get_contrasting_text_color( $color_hex );
                $bcolor_hex = $datos_tipo['border_color'] ?? $color_hex;
                $bwidth_val = $datos_tipo['border_width'] ?? '0px';
                $brad_val   = $datos_tipo['border_radius'] ?? '4px';
                $style_prev = sprintf( 'background-color:%1$s;color:%2$s;border:%3$s solid %4$s;border-radius:%5$s;', esc_attr( $color_hex ), esc_attr( $text_hex ), esc_attr( $bwidth_val ), esc_attr( $bcolor_hex ), esc_attr( $brad_val ) );
                if ( preg_match( '/^0(?:px|rem|em|%)?$/', $bwidth_val ) ) {
                    $style_prev .= 'border-left:4px solid ' . esc_attr( $bcolor_hex ) . ';';
                }
                ?>
                <div class="cdb-config-mensaje<?php echo ( '1' !== $mostrar ) ? ' oculto' : ''; ?>" id="mensaje-<?php echo esc_attr( $id ); ?>">
                    <strong><?php echo esc_html( $datos['label'] ); ?></strong> <span class="cdb-oculto-label"><?php esc_html_e( 'Oculto', 'cdb-form' ); ?></span>
                    <div class="cdb-aviso cdb-mensaje-preview <?php echo esc_attr( $clase ); ?>" style="<?php echo esc_attr( $style_prev ); ?>">
                        <strong class="cdb-mensaje-destacado"><?php echo wp_kses_post( $texto ); ?></strong>
                        <span class="cdb-mensaje-secundario" <?php if ( empty( $secundario ) ) echo 'style="display:none;"'; ?>><?php echo wp_kses_post( $secundario ); ?></span>
                    </div>
                    <button type="button" class="button cdb-edit-mensaje"><?php esc_html_e( 'Editar', 'cdb-form' ); ?></button>
                    <div class="cdb-mensaje-edicion" style="display:none;">
                        <label><?php esc_html_e( 'Frase destacada', 'cdb-form' ); ?></label>
                        <textarea class="large-text" rows="2" name="<?php echo esc_attr( $datos['text_option'] ); ?>" data-role="destacado" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( $texto ); ?></textarea>
                        <label><?php esc_html_e( 'Frase secundaria', 'cdb-form' ); ?></label>
                        <textarea class="large-text" rows="2" name="<?php echo esc_attr( $sec_opt ); ?>" data-role="secundario"><?php echo esc_textarea( $secundario ); ?></textarea>
                        <p class="description"><?php echo esc_html( $datos['description'] ); ?></p>
                        <p class="description"><em><?php esc_html_e( 'Traducción actual:', 'cdb-form' ); ?></em> <?php echo esc_html( $traduccion_i18n ); ?></p>
                        <label><?php esc_html_e( 'Tipo/Color', 'cdb-form' ); ?></label>
                        <select name="<?php echo esc_attr( $datos['color_option'] ); ?>">
                            <?php foreach ( $tipos_color as $slug => $info ) : ?>
                                <option value="<?php echo esc_attr( $slug ); ?>" data-bg="<?php echo esc_attr( $info['bg'] ); ?>" data-text="<?php echo esc_attr( $info['text'] ); ?>" data-class="<?php echo esc_attr( $info['class'] ); ?>" data-bordercolor="<?php echo esc_attr( $info['border_color'] ); ?>" data-borderwidth="<?php echo esc_attr( $info['border_width'] ); ?>" data-borderradius="<?php echo esc_attr( $info['border_radius'] ); ?>" style="color: <?php echo esc_attr( $info['bg'] ); ?>;" <?php selected( $tipo, $slug ); ?>>&#11044; <?php echo esc_html( $info['name'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Clase CSS:', 'cdb-form' ); ?> <code class="cdb-clase-css"><?php echo esc_html( $clase ); ?></code></p>
                        <label><input type="checkbox" name="<?php echo esc_attr( $show_opt ); ?>" value="1" <?php checked( $mostrar, '1' ); ?> data-role="mostrar" /> <?php esc_html_e( 'Mostrar aviso', 'cdb-form' ); ?></label>
                    </div>
                </div>
            <?php endforeach; ?>

            <h2><?php esc_html_e( 'Tipos/Colores', 'cdb-form' ); ?></h2>
            <p class="description"><?php esc_html_e( 'Selecciona colores de fondo y texto con suficiente contraste para garantizar la legibilidad.', 'cdb-form' ); ?></p>
            <div id="cdb-tipos-color">
                <?php foreach ( $tipos_color as $slug => $info ) : ?>
                    <div class="cdb-tipo-color-row">
                        <span class="cdb-color-swatch" style="background-color: <?php echo esc_attr( $info['bg'] ); ?>"></span>
                        <input type="text" name="tipos_color[<?php echo esc_attr( $slug ); ?>][name]" value="<?php echo esc_attr( $info['name'] ); ?>" />
                        <input type="text" name="tipos_color[<?php echo esc_attr( $slug ); ?>][class]" value="<?php echo esc_attr( $info['class'] ); ?>" />
                        <input type="color" name="tipos_color[<?php echo esc_attr( $slug ); ?>][bg]" value="<?php echo esc_attr( $info['bg'] ); ?>" />
                        <input type="color" name="tipos_color[<?php echo esc_attr( $slug ); ?>][text]" value="<?php echo esc_attr( $info['text'] ); ?>" />
                        <input type="text" name="tipos_color[<?php echo esc_attr( $slug ); ?>][border_color]" class="cdb-border-color" value="<?php echo esc_attr( $info['border_color'] ); ?>" />
                        <input type="text" name="tipos_color[<?php echo esc_attr( $slug ); ?>][border_width]" list="cdb-border-width" value="<?php echo esc_attr( $info['border_width'] ); ?>" />
                        <input type="text" name="tipos_color[<?php echo esc_attr( $slug ); ?>][border_radius]" list="cdb-border-radius" value="<?php echo esc_attr( $info['border_radius'] ); ?>" />
                        <label><input type="checkbox" name="tipos_color[<?php echo esc_attr( $slug ); ?>][delete]" value="1" /><?php esc_html_e( 'Eliminar', 'cdb-form' ); ?></label>
                        <span class="cdb-contrast-warning"><?php esc_html_e( 'Contraste bajo', 'cdb-form' ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <datalist id="cdb-border-width">
                <option value="0px"></option>
                <option value="1px"></option>
                <option value="2px"></option>
                <option value="4px"></option>
            </datalist>
            <datalist id="cdb-border-radius">
                <option value="0px"></option>
                <option value="4px"></option>
                <option value="6px"></option>
                <option value="8px"></option>
            </datalist>
            <p><button type="button" class="button" id="cdb-add-tipo-color"><?php esc_html_e( 'Añadir tipo/color', 'cdb-form' ); ?></button></p>

            <?php submit_button( __( 'Guardar cambios', 'cdb-form' ) ); ?>
        </form>
    </div>
    <?php
}
