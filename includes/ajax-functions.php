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
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'cdb-form' ) ) );
        }
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'cdb_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Error de seguridad.', 'cdb-form' ) ) );
        }
        if ( ! isset( $_POST['empleado_id'] ) || ! isset( $_POST['disponible'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Datos inválidos.', 'cdb-form' ) ) );
        }
        
        $empleado_id  = intval( $_POST['empleado_id'] );
        $disponible   = ( $_POST['disponible'] == "1" ) ? 1 : 0;
        $current_user = wp_get_current_user();
        $empleado     = get_post( $empleado_id );
        
        if ( ! $empleado || $empleado->post_author != $current_user->ID ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para editar este empleado.', 'cdb-form' ) ) );
        }
        
        $resultado = update_post_meta( $empleado_id, 'disponible', $disponible );
        if ( $resultado !== false ) {
            wp_send_json_success( array( 'message' => __( 'Disponibilidad actualizada correctamente.', 'cdb-form' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error al actualizar la disponibilidad.', 'cdb-form' ) ) );
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
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para realizar esta acción.', 'cdb-form' ) ) );
        }
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'cdb_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Error de seguridad.', 'cdb-form' ) ) );
        }
        if ( ! isset( $_POST['bar_id'] ) || ! isset( $_POST['estado'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Datos inválidos.', 'cdb-form' ) ) );
        }
        
        $bar_id       = intval( $_POST['bar_id'] );
        $estado       = sanitize_text_field( $_POST['estado'] );
        $current_user = wp_get_current_user();
        $bar          = get_post( $bar_id );
        
        if ( ! $bar || $bar->post_author != $current_user->ID ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos para editar este bar.', 'cdb-form' ) ) );
        }
        
        $resultado = update_post_meta( $bar_id, 'estado', $estado );
        if ( $resultado !== false ) {
            wp_send_json_success( array( 'message' => __( 'Estado del bar actualizado correctamente.', 'cdb-form' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Error al actualizar el estado del bar.', 'cdb-form' ) ) );
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
            wp_send_json_error( array( 'message' => __( 'Bar ID no proporcionado.', 'cdb-form' ) ) );
        }
        
        $bar_id         = intval( $_GET['bar_id'] );
        $fecha_apertura = get_post_meta( $bar_id, '_cdb_bar_apertura', true );
        $fecha_cierre   = get_post_meta( $bar_id, '_cdb_bar_cierre', true );
        
        if ( ! $fecha_apertura ) {
            wp_send_json_error( array( 'message' => __( 'No se encontraron fechas para este bar.', 'cdb-form' ) ) );
        }
        
        $fecha_apertura = intval( $fecha_apertura );
        $fecha_cierre = $fecha_cierre ? intval( $fecha_cierre ) : '';
        
        wp_send_json_success( array(
            'fecha_apertura' => $fecha_apertura,
            'fecha_cierre'   => $fecha_cierre
        ) );
        wp_die();
    }
}
add_action( 'wp_ajax_cdb_obtener_anios_bar', 'cdb_obtener_anios_bar' );

/**
 * Guarda una experiencia laboral.
 *
 * Se esperan los parámetros POST 'empleado_id', 'bar_id', 'anio' y 'posicion_id'.
 * Tras la inserción, se vincula con el equipo correspondiente, se integra la puntuación de la zona
 * (sumándola a la puntuación de la posición) y se actualiza el meta "cdb_experiencia_score".
 */
if ( ! function_exists( 'cdb_guardar_experiencia' ) ) {
    function cdb_guardar_experiencia() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'No tienes permisos.', 'cdb-form' ) ) );
        }
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'cdb_form_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Error de seguridad.', 'cdb-form' ) ) );
        }
        
        $empleado_id = intval( $_POST['empleado_id'] );
        $bar_id      = intval( $_POST['bar_id'] );
        $anio        = intval( $_POST['anio'] );
        $posicion_id = intval( $_POST['posicion_id'] );
        
        global $wpdb;
        $tabla_exp = $wpdb->prefix . 'cdb_experiencia';
        
        $resultado_insert = $wpdb->insert(
            $tabla_exp,
            array(
                'empleado_id' => $empleado_id,
                'bar_id'      => $bar_id,
                'anio'        => $anio,
                'posicion_id' => $posicion_id,
            ),
            array( '%d', '%d', '%d', '%d' )
        );
        
        if ( $resultado_insert ) {
            // Vincular con el equipo (bar + año) si es posible.
            if ( function_exists('cdb_get_or_create_equipo') ) {
                $equipo_id = cdb_get_or_create_equipo( $bar_id, $anio );
                update_post_meta( $empleado_id, '_cdb_empleado_equipo', $equipo_id );
                update_post_meta( $empleado_id, '_cdb_empleado_year', $anio );
                update_post_meta( $empleado_id, '_cdb_empleado_bar', $bar_id );
            }
            
            /**
             * Integración de la puntuación de la zona:
             * - Se obtiene la puntuación de la posición asociada (ya almacenada en _cdb_posiciones_score).
             * - Se obtiene la puntuación de la zona asignada al Bar.
             * - Se suman ambas para obtener la puntuación total de la experiencia.
             */
            $puntuacion_posicion = get_post_meta( $posicion_id, '_cdb_posiciones_score', true );
            $puntuacion_posicion = intval( $puntuacion_posicion );
            
            $puntuacion_zona = 0;
            if ( $bar_id ) {
                $zona_id = get_post_meta( $bar_id, '_cdb_bar_zona_id', true );
                if ( $zona_id ) {
                    $puntuacion_zona = get_post_meta( $zona_id, 'puntuacion_zona', true );
                    $puntuacion_zona = intval( $puntuacion_zona );
                }
            }
            $experiencia_total = $puntuacion_posicion + $puntuacion_zona;
            
            // Se podría guardar este valor en un meta field propio de la experiencia si fuera necesario.
            // update_post_meta( $experiencia_id, '_cdb_experiencia_total', $experiencia_total );
            
            // Actualizar el meta "cdb_experiencia_score" del empleado.
            cdb_actualizar_experiencia_score( $empleado_id );
            wp_send_json_success( array( 'message' => __( 'Experiencia guardada correctamente.', 'cdb-form' ), 'experiencia_total' => $experiencia_total ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'No se pudo guardar la experiencia.', 'cdb-form' ) ) );
        }
        wp_die();
    }
}
add_action( 'wp_ajax_cdb_guardar_experiencia', 'cdb_guardar_experiencia' );
add_action( 'wp_ajax_nopriv_cdb_guardar_experiencia', 'cdb_guardar_experiencia' );

/**
 * Borra una experiencia laboral.
 *
 * Se espera el parámetro POST 'exp_id'. Se valida que la experiencia pertenezca al empleado del usuario actual.
 * Tras el borrado, se actualiza el meta "cdb_experiencia_score".
 */
if ( ! function_exists( 'cdb_borrar_experiencia' ) ) {
    function cdb_borrar_experiencia() {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array('message' => __( 'No tienes permisos (no logueado).', 'cdb-form' ) ) );
        }
        if ( ! isset($_POST['security']) || ! wp_verify_nonce($_POST['security'], 'cdb_form_nonce') ) {
            wp_send_json_error( array('message' => __( 'Error de seguridad (nonce).', 'cdb-form' ) ) );
        }
        if ( ! isset($_POST['exp_id']) ) {
            wp_send_json_error( array('message' => __( 'ID de experiencia no proporcionado.', 'cdb-form' ) ) );
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
            wp_send_json_error( array('message' => __( 'No tienes permiso para eliminar esa experiencia.', 'cdb-form' ) ) );
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
            wp_send_json_success( array( 'message' => __( 'Experiencia eliminada correctamente.', 'cdb-form' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'No se pudo eliminar la experiencia.', 'cdb-form' ) ) );
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
            wp_die( __( 'Debes iniciar sesión para ver la experiencia.', 'cdb-form' ), 403 );
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
                                <?php esc_html_e( 'Borrar', 'cdb-form' ); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p><?php esc_html_e( 'No tienes experiencia registrada aún.', 'cdb-form' ); ?></p>
        <?php
        endif;
        $html = ob_get_clean();
        echo $html;
        wp_die();
    }
}
add_action( 'wp_ajax_cdb_listar_experiencias', 'cdb_listar_experiencias' );
add_action( 'wp_ajax_nopriv_cdb_listar_experiencias', 'cdb_listar_experiencias' );

/**
 * Devuelve resultados HTML para el buscador avanzado de empleados.
 */
function cdb_buscar_empleados_ajax() {
    if ( ! is_user_logged_in() ) {
        error_log( 'cdb_buscar_empleados_ajax: usuario no autenticado' );
        wp_send_json_error( array( 'message' => __( 'Debe iniciar sesión.', 'cdb-form' ) ), 403 );
    }
    if ( ! check_ajax_referer( 'cdb_form_nonce', 'nonce', false ) ) {
        error_log( 'cdb_buscar_empleados_ajax: nonce incorrecto' );
        wp_send_json_error( array( 'message' => __( 'Nonce incorrecto', 'cdb-form' ) ) );
    }

    $args = array(
        'nombre'      => isset( $_GET['nombre'] ) ? sanitize_text_field( $_GET['nombre'] ) : '',
        'posicion_id' => isset( $_GET['posicion_id'] ) ? intval( $_GET['posicion_id'] ) : 0,
        'bar_id'      => isset( $_GET['bar_id'] ) ? intval( $_GET['bar_id'] ) : 0,
        'anio'        => isset( $_GET['anio'] ) ? intval( $_GET['anio'] ) : 0,
    );

    $empleados = cdb_busqueda_empleados_get_datos( $args );

    ob_start();
    include CDB_FORM_PATH . 'templates/busqueda-empleados-table.php';
    $html = ob_get_clean();

    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_cdb_buscar_empleados', 'cdb_buscar_empleados_ajax' );

/**
 * Devuelve resultados HTML para el buscador avanzado de bares.
 */
function cdb_buscar_bares_ajax() {
    if ( ! is_user_logged_in() ) {
        error_log( 'cdb_buscar_bares_ajax: usuario no autenticado' );
        wp_send_json_error( array( 'message' => __( 'Debe iniciar sesión.', 'cdb-form' ) ), 403 );
    }
    if ( ! check_ajax_referer( 'cdb_form_nonce', 'nonce', false ) ) {
        error_log( 'cdb_buscar_bares_ajax: nonce incorrecto' );
        wp_send_json_error( array( 'message' => __( 'Nonce incorrecto', 'cdb-form' ) ) );
    }

    $args = array(
        'nombre'     => isset( $_GET['nombre'] ) ? sanitize_text_field( $_GET['nombre'] ) : '',
        'zona_id'    => isset( $_GET['zona_id'] ) ? intval( $_GET['zona_id'] ) : 0,
        'apertura'   => isset( $_GET['apertura'] ) ? intval( $_GET['apertura'] ) : 0,
        'reputacion' => isset( $_GET['reputacion'] ) ? sanitize_text_field( $_GET['reputacion'] ) : '',
    );

    $bares = cdb_busqueda_bares_get_datos( $args );

    ob_start();
    include CDB_FORM_PATH . 'templates/busqueda-bares-table.php';
    $html = ob_get_clean();

    wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_cdb_buscar_bares', 'cdb_buscar_bares_ajax' );

/**
 * Proporciona sugerencias de autocompletado.
 */
function cdb_busqueda_sugerencias_ajax() {
    if ( ! is_user_logged_in() ) {
        error_log( 'cdb_busqueda_sugerencias_ajax: usuario no autenticado' );
        wp_send_json_error( array( 'message' => __( 'Debe iniciar sesión.', 'cdb-form' ) ), 403 );
    }
    if ( ! check_ajax_referer( 'cdb_form_nonce', 'nonce', false ) ) {
        error_log( 'cdb_busqueda_sugerencias_ajax: nonce incorrecto' );
        wp_send_json_error( array( 'message' => __( 'Nonce incorrecto', 'cdb-form' ) ) );
    }

    $tipo = isset( $_GET['tipo'] ) ? sanitize_text_field( $_GET['tipo'] ) : '';
    $term = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';

    $results = array();
    // Dependiendo del tipo se consultan los posts o la tabla de experiencia.
    // Para añadir nuevos filtros, basta con agregar otro "case" que devuelva
    // las opciones pertinentes.
    switch ( $tipo ) {
        case 'nombre':
            $ids = get_posts( array(
                'post_type'   => 'empleado',
                'post_status' => 'publish',
                's'           => $term,
                'numberposts' => 10,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'fields'      => 'ids'
            ) );
            foreach ( $ids as $id ) {
                $results[] = array( 'label' => get_the_title( $id ), 'value' => get_the_title( $id ) );
            }
            break;
        case 'posicion':
            $ids = get_posts( array(
                'post_type'   => 'cdb_posiciones',
                'post_status' => 'publish',
                's'           => $term,
                'numberposts' => 10,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'fields'      => 'ids'
            ) );
            foreach ( $ids as $id ) {
                $results[] = array( 'label' => get_the_title( $id ), 'value' => get_the_title( $id ), 'id' => $id );
            }
            break;
        case 'bar':
            $ids = get_posts( array(
                'post_type'   => 'bar',
                'post_status' => 'publish',
                's'           => $term,
                'numberposts' => 10,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'fields'      => 'ids'
            ) );
            foreach ( $ids as $id ) {
                $results[] = array( 'label' => get_the_title( $id ), 'value' => get_the_title( $id ), 'id' => $id );
            }
            break;
        case 'anio':
            global $wpdb;
            $tabla_exp = $wpdb->prefix . 'cdb_experiencia';
            $years = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT anio FROM {$tabla_exp} WHERE anio LIKE %s ORDER BY anio DESC LIMIT 10", $term . '%' ) );
            foreach ( $years as $y ) {
                $results[] = array( 'label' => $y, 'value' => $y );
            }
            break;
        case 'zona':
            $ids = get_posts( array(
                'post_type'   => 'zona',
                'post_status' => 'publish',
                's'           => $term,
                'numberposts' => 10,
                'orderby'     => 'title',
                'order'       => 'ASC',
                'fields'      => 'ids'
            ) );
            foreach ( $ids as $id ) {
                $results[] = array( 'label' => get_the_title( $id ), 'value' => get_the_title( $id ), 'id' => $id );
            }
            break;
        case 'apertura':
            global $wpdb;
            $years = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_cdb_bar_apertura' AND meta_value LIKE %s ORDER BY meta_value DESC LIMIT 10", $term . '%' ) );
            foreach ( $years as $y ) {
                $results[] = array( 'label' => $y, 'value' => $y );
            }
            break;
        case 'reputacion':
            global $wpdb;
            $vals = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'reputacion' AND meta_value LIKE %s ORDER BY meta_value+0 DESC LIMIT 10", $term . '%' ) );
            foreach ( $vals as $v ) {
                $results[] = array( 'label' => $v, 'value' => $v );
            }
            break;
    }

    wp_send_json( $results );
}
add_action( 'wp_ajax_cdb_sugerencias', 'cdb_busqueda_sugerencias_ajax' );
