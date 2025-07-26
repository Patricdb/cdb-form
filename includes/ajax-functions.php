<?php
// Evitar acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Recalcula y actualiza el meta field 'cdb_experiencia_score' para un empleado.
 *
 * Se suman los valores de '_cdb_posiciones_score' de cada experiencia registrada en la tabla personalizada.
 *
 * @param int $empleado_id ID del empleado (post).
 * @return int La puntuación total actualizada.
 */
function cdb_actualizar_experiencia_score( $empleado_id ) {
    global $wpdb;
    $tabla_exp = $wpdb->prefix . 'cdb_experiencia';
    $total = 0;
    
    // Obtener posicion_id y bar_id para cada experiencia
    $experiencias = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT posicion_id, bar_id
             FROM {$tabla_exp}
             WHERE empleado_id = %d",
            $empleado_id
        )
    );
    
    if ( ! empty( $experiencias ) ) {
        foreach ( $experiencias as $exp ) {
            // 1. Puntuación de la posición
            $posicion_score = intval( get_post_meta( $exp->posicion_id, '_cdb_posiciones_score', true ) );
            
            // 2. Puntuación de la zona
            $zona_score = 0;
            if ( ! empty( $exp->bar_id ) ) {
                $zona_id = get_post_meta( $exp->bar_id, '_cdb_bar_zona_id', true );
                if ( $zona_id ) {
                    $zona_score = intval( get_post_meta( $zona_id, 'puntuacion_zona', true ) );
                }
            }

            // 3. Puntuación total del Bar
            // Ajusta el meta key si en tu instalación se llama de otra forma
            $bar_score = 0;
            if ( ! empty( $exp->bar_id ) ) {
                $bar_score = intval( get_post_meta( $exp->bar_id, 'cdb_puntuacion_total', true ) );
            }
            
            // Sumar posición + zona + puntuación del bar
            $total += ( $posicion_score + $zona_score + $bar_score );
        }
    }
    
    // Guardar la suma final en 'cdb_experiencia_score'
    update_post_meta( $empleado_id, 'cdb_experiencia_score', $total );
    return $total;
}

/**
 * Actualiza la disponibilidad de un empleado.
 *
 * Se esperan los parámetros POST 'empleado_id' y 'disponible'. Valida el nonce y el usuario.
 */
if ( ! function_exists( 'cdb_actualizar_disponibilidad' ) ) {
    function cdb_actualizar_disponibilidad() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'No tienes permisos para realizar esta acción.' ) );
        }
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'cdb_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Error de seguridad.' ) );
        }
        if ( ! isset( $_POST['empleado_id'] ) || ! isset( $_POST['disponible'] ) ) {
            wp_send_json_error( array( 'message' => 'Datos inválidos.' ) );
        }
        
        $empleado_id  = intval( $_POST['empleado_id'] );
        $disponible   = ( $_POST['disponible'] == "1" ) ? 1 : 0;
        $current_user = wp_get_current_user();
        $empleado     = get_post( $empleado_id );
        
        if ( ! $empleado || $empleado->post_author != $current_user->ID ) {
            wp_send_json_error( array( 'message' => 'No tienes permisos para editar este empleado.' ) );
        }
        
        $resultado = update_post_meta( $empleado_id, 'disponible', $disponible );
        if ( $resultado !== false ) {
            wp_send_json_success( array( 'message' => 'Disponibilidad actualizada correctamente.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Error al actualizar la disponibilidad.' ) );
        }
        wp_die();
    }
}
add_action( 'wp_ajax_cdb_actualizar_disponibilidad', 'cdb_actualizar_disponibilidad' );

/**
 * Actualiza el estado de un bar.
 *
 * Se esperan los parámetros POST 'bar_id' y 'estado'. Se valida el nonce y los permisos.
 */
if ( ! function_exists( 'cdb_actualizar_estado_bar' ) ) {
    function cdb_actualizar_estado_bar() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'No tienes permisos para realizar esta acción.' ) );
        }
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'cdb_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Error de seguridad.' ) );
        }
        if ( ! isset( $_POST['bar_id'] ) || ! isset( $_POST['estado'] ) ) {
            wp_send_json_error( array( 'message' => 'Datos inválidos.' ) );
        }
        
        $bar_id       = intval( $_POST['bar_id'] );
        $estado       = sanitize_text_field( $_POST['estado'] );
        $current_user = wp_get_current_user();
        $bar          = get_post( $bar_id );
        
        if ( ! $bar || $bar->post_author != $current_user->ID ) {
            wp_send_json_error( array( 'message' => 'No tienes permisos para editar este bar.' ) );
        }
        
        $resultado = update_post_meta( $bar_id, 'estado', $estado );
        if ( $resultado !== false ) {
            wp_send_json_success( array( 'message' => 'Estado del bar actualizado correctamente.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'Error al actualizar el estado del bar.' ) );
        }
        wp_die();
    }
}
add_action( 'wp_ajax_cdb_actualizar_estado_bar', 'cdb_actualizar_estado_bar' );

/**
 * Obtiene los años disponibles para un bar basado en sus fechas de apertura y cierre.
 *
 * Se espera un parámetro GET 'bar_id'.
 */
if ( ! function_exists( 'cdb_obtener_anios_bar' ) ) {
    function cdb_obtener_anios_bar() {
        if ( ! isset( $_GET['bar_id'] ) ) {
            wp_send_json_error( array( 'message' => 'Bar ID no proporcionado.' ) );
        }

        $bar_id = intval( $_GET['bar_id'] );
        if ( ! $bar_id ) {
            wp_send_json_error( array( 'message' => 'ID de bar inválido.' ) );
        }

        $fecha_apertura = get_post_meta( $bar_id, '_cdb_bar_apertura', true );
        $fecha_cierre   = get_post_meta( $bar_id, '_cdb_bar_cierre', true );

        if ( ! $fecha_apertura ) {
            wp_send_json_error( array( 'message' => 'No se encontraron fechas para este bar.' ) );
        }

        $fecha_apertura = intval( $fecha_apertura );
        $fecha_cierre   = $fecha_cierre !== '' ? intval( $fecha_cierre ) : '';

        if ( $fecha_cierre && $fecha_cierre < $fecha_apertura ) {
            wp_send_json_error( array( 'message' => 'La fecha de cierre es anterior a la de apertura.' ) );
        }

        wp_send_json_success( array(
            'fecha_apertura' => $fecha_apertura,
            'fecha_cierre'   => $fecha_cierre,
        ) );
        wp_die();
    }
}
add_action( 'wp_ajax_cdb_obtener_anios_bar', 'cdb_obtener_anios_bar' );

/**
 * Borra una experiencia laboral.
 *
 * Se espera el parámetro POST 'exp_id'. Se valida que la experiencia pertenezca al empleado del usuario actual.
 * Tras el borrado, se actualiza el meta "cdb_experiencia_score".
 */
if ( ! function_exists( 'cdb_borrar_experiencia' ) ) {
    function cdb_borrar_experiencia() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array('message' => 'No tienes permisos (no logueado).') );
        }
        if ( ! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'cdb_form_nonce') ) {
            wp_send_json_error( array('message' => 'Error de seguridad (nonce).') );
        }
        if ( ! isset($_POST['exp_id']) ) {
            wp_send_json_error( array('message' => 'ID de experiencia no proporcionado.') );
        }
        $exp_id = intval($_POST['exp_id']);
        global $wpdb;
        $tabla_exp = $wpdb->prefix . 'cdb_experiencia';
        
        // Validar que la experiencia pertenece al empleado actual.
        $empleado_id = cdb_obtener_empleado_id( get_current_user_id() );
        $fila = $wpdb->get_row(
            $wpdb->prepare("SELECT empleado_id FROM {$tabla_exp} WHERE id = %d", $exp_id)
        );
        if ( ! $fila || $fila->empleado_id != $empleado_id ) {
            wp_send_json_error( array('message' => 'No tienes permiso para eliminar esa experiencia.') );
        }
        
        // Borrar el registro.
        $borrado = $wpdb->delete(
            $tabla_exp,
            array( 'id' => $exp_id ),
            array( '%d' )
        );
        if ( $borrado ) {
            // Actualizar el meta "cdb_experiencia_score".
            cdb_actualizar_experiencia_score( $empleado_id );
            wp_send_json_success( array( 'message' => 'Experiencia eliminada correctamente.' ) );
        } else {
            wp_send_json_error( array( 'message' => 'No se pudo eliminar la experiencia.' ) );
        }
        wp_die();
    }
}
add_action('wp_ajax_cdb_borrar_experiencia', 'cdb_borrar_experiencia');

/**
 * Genera y retorna una tabla HTML con las experiencias registradas de un empleado.
 *
 * Se espera un parámetro GET 'empleado_id'.
 */
if ( ! function_exists( 'cdb_listar_experiencias' ) ) {
    function cdb_listar_experiencias() {
        if ( ! is_user_logged_in() ) {
            wp_die( 'Debes iniciar sesión para ver la experiencia.', 403 );
        }
        global $wpdb;
        $empleado_id = isset( $_GET['empleado_id'] ) ? intval( $_GET['empleado_id'] ) : 0;
        $experiencias = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT exp.id AS exp_id,
                        exp.anio,
                        p.post_title AS bar,
                        pos.post_title AS posicion
                 FROM {$wpdb->prefix}cdb_experiencia exp
                 JOIN {$wpdb->prefix}posts p ON exp.bar_id = p.ID
                 JOIN {$wpdb->prefix}posts pos ON exp.posicion_id = pos.ID
                 WHERE exp.empleado_id = %d
                   AND p.post_type = 'bar'
                   AND p.post_status = 'publish'
                   AND pos.post_type = 'cdb_posiciones'
                   AND pos.post_status = 'publish'
                 ORDER BY exp.anio DESC",
                $empleado_id
            )
        );
        ob_start();
        if ( ! empty( $experiencias ) ) : ?>
            <table>
                <thead>
                    <tr>
                        <th>Año</th>
                        <th>Bar</th>
                        <th>Posición</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $experiencias as $exp ) : ?>
                    <tr>
                        <td><?php echo esc_html( $exp->anio ); ?></td>
                        <td><?php echo esc_html( $exp->bar ); ?></td>
                        <td><?php echo esc_html( $exp->posicion ); ?></td>
                        <td>
                            <!-- Botón para borrar experiencia -->
                            <button class="cdb-btn-borrar" data-exp-id="<?php echo esc_attr($exp->exp_id); ?>">
                                Borrar
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No tienes experiencia registrada aún.</p>
        <?php
        endif;
        $html = ob_get_clean();
        echo $html;
        wp_die();
    }
}
add_action( 'wp_ajax_cdb_listar_experiencias', 'cdb_listar_experiencias' );
add_action( 'wp_ajax_nopriv_cdb_listar_experiencias', 'cdb_listar_experiencias' );
