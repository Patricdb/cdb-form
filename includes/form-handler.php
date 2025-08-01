<?php
/**
 * includes/form-handler.php
 *
 * Archivo que maneja los formularios AJAX para guardar la experiencia y el perfil de empleado.
 * Se asegura de que el código se ejecute únicamente en el contexto de WordPress.
 */

// Evitar acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit;
}

// Incluir el archivo que contiene la función cdb_actualizar_experiencia_score si no está cargado.
if (!function_exists('cdb_actualizar_experiencia_score')) {
    require_once plugin_dir_path(__FILE__) . 'ajax-functions.php';
}

/**
 * Función para guardar la experiencia laboral.
 *
 * Se valida el nonce, se obtiene el empleado de forma consistente mediante cdb_obtener_empleado_id(),
 * se inserta el registro en la tabla personalizada wp_cdb_experiencia y se actualizan los metadatos relacionados
 * (incluyendo la vinculación al CPT "equipo" y el meta "cdb_experiencia_score").
 * Finalmente, se envía una respuesta JSON que indica que se debe recargar la página.
 */
function cdb_guardar_experiencia() {
    // 1. Verificar nonce para seguridad.
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'cdb_form_nonce')) {
        wp_send_json_error(['message' => 'Error de seguridad.']);
        wp_die();
    }

    // 2. Obtener el usuario actual.
    $current_user = wp_get_current_user();
    if (!$current_user->exists()) {
        wp_send_json_error(['message' => 'Debes iniciar sesión para completar este formulario.']);
        wp_die();
    }

    // 3. Validar que los campos obligatorios se hayan enviado.
    if (!isset($_POST['bar_id'], $_POST['posicion_id'], $_POST['anio'])) {
        wp_send_json_error(['message' => 'Todos los campos son obligatorios.']);
        wp_die();
    }

    // 4. Obtener el ID del empleado de forma consistente (evitar depender del valor enviado por POST).
    $empleado_id = cdb_obtener_empleado_id($current_user->ID);
    $bar_id      = intval($_POST['bar_id']);
    $posicion_id = intval($_POST['posicion_id']);
    $anio        = intval($_POST['anio']);

    error_log("DEBUG: Procesando registro de experiencia para Empleado ID: $empleado_id, Bar ID: $bar_id, Posición ID: $posicion_id, Año: $anio");

    // 5. Verificar que el usuario está editando su propio perfil.
    $post = get_post($empleado_id);
    if (!$post || $post->post_author != $current_user->ID) {
        wp_send_json_error(['message' => 'No tienes permisos para editar este perfil.']);
        wp_die();
    }

    // 6. Insertar el registro en la tabla personalizada wp_cdb_experiencia.
    global $wpdb;
    $tabla_exp = "{$wpdb->prefix}cdb_experiencia";
    $resultado = $wpdb->insert(
        $tabla_exp,
        array(
            'empleado_id'        => $empleado_id,
            'bar_id'             => $bar_id,
            'posicion_id'        => $posicion_id,
            'anio'               => $anio,
            'fecha_creacion'     => current_time('mysql'),
            'fecha_modificacion' => current_time('mysql')
        ),
        array('%d', '%d', '%d', '%d', '%s', '%s')
    );

    // 7. Verificar si ocurrió algún error durante la inserción.
    if ($wpdb->last_error) {
        error_log("[ERROR] Fallo al insertar experiencia: " . $wpdb->last_error);
        wp_send_json_error(['message' => 'Error al registrar la experiencia.']);
        wp_die();
    }

    // 8. Si la inserción fue exitosa, proceder a vincular el registro con el CPT "equipo" y actualizar metadatos.
    if ($resultado) {
        error_log("[DEBUG] Experiencia insertada en wp_cdb_experiencia para Empleado ID: $empleado_id");

        // Vincular con el CPT "equipo" si la función existe.
        if (function_exists('cdb_get_or_create_equipo')) {
            $equipo_id = cdb_get_or_create_equipo($bar_id, $anio);

            // 8.1 Actualizar la fila recién insertada con el equipo_id.
            $exp_id = $wpdb->insert_id;
            $wpdb->update(
                $tabla_exp,
                array('equipo_id' => $equipo_id),
                array('id' => $exp_id),
                array('%d'),
                array('%d')
            );

            // 8.2 Actualizar los metadatos del empleado.
            update_post_meta($empleado_id, '_cdb_empleado_equipo', $equipo_id);
            update_post_meta($empleado_id, '_cdb_empleado_year', $anio);
            update_post_meta($empleado_id, '_cdb_empleado_bar', $bar_id);
        }

        // 9. Actualizar el meta "cdb_experiencia_score" del empleado.
        cdb_actualizar_experiencia_score($empleado_id);
        $nuevo_score = get_post_meta($empleado_id, 'cdb_experiencia_score', true);
        error_log("DEBUG: Nuevo cdb_experiencia_score para Empleado ID $empleado_id: $nuevo_score");

        // 10. Enviar respuesta JSON indicando éxito y solicitando la recarga de la página.
        wp_send_json_success([
            'message' => 'Experiencia registrada correctamente.',
            'reload'  => true // Instrucción para que el JS recargue la página.
        ]);
    } else {
        wp_send_json_error(['message' => 'No se pudo guardar la experiencia.']);
    }

    wp_die();
}
add_action('wp_ajax_cdb_guardar_experiencia', 'cdb_guardar_experiencia');
add_action('wp_ajax_nopriv_cdb_guardar_experiencia', 'cdb_guardar_experiencia');


/**
 * Función para manejar el envío y actualización del perfil de empleado.
 *
 * Esta función se utiliza tanto para crear como para actualizar el perfil de empleado.
 */
function cdb_form_empleado_submit() {
    // Verificar nonce para seguridad.
    check_ajax_referer('cdb_form_nonce', 'security');

    $current_user = wp_get_current_user();
    if (!$current_user->exists()) {
        wp_send_json_error(['message' => 'Debes iniciar sesión para completar este formulario.']);
        wp_die();
    }

    // Recoger y sanitizar los datos del formulario.
    $empleado_id = isset($_POST['empleado_id']) ? intval($_POST['empleado_id']) : 0;
    $nombre      = isset($_POST['nombre']) ? sanitize_text_field($_POST['nombre']) : '';
    $disponible  = isset($_POST['disponible']) ? filter_var($_POST['disponible'], FILTER_VALIDATE_BOOLEAN) : false;
    $bar_id      = isset($_POST['bar_id']) ? intval($_POST['bar_id']) : 0;
    $posicion_id = isset($_POST['posicion_id']) ? intval($_POST['posicion_id']) : 0;
    $anio        = date('Y');

    // Validar campo obligatorio.
    if (empty($nombre)) {
        wp_send_json_error(['message' => 'El nombre es obligatorio.']);
        wp_die();
    }

    error_log("DEBUG: Procesando perfil de empleado: Nombre: $nombre, Disponible: $disponible, Bar ID: $bar_id, Posición ID: $posicion_id, Año: $anio");

    // CREAR PERFIL DE EMPLEADO SI NO EXISTE.
    if ($empleado_id === 0) {
        $existing_empleado = get_posts([
            'post_type'      => 'empleado',
            'author'         => $current_user->ID,
            'posts_per_page' => 1
        ]);
        if (!empty($existing_empleado)) {
            wp_send_json_error(['message' => 'Ya tienes un perfil de empleado. No puedes crear más de uno.']);
            wp_die();
        }

        $post_data = [
            'post_title'  => $nombre,
            'post_type'   => 'empleado',
            'post_status' => 'publish',
            'post_author' => $current_user->ID
        ];

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[DEBUG] wp_insert_post data: ' . print_r($post_data, true));
        }

        $empleado_id = wp_insert_post($post_data);

        if (defined('WP_DEBUG') && WP_DEBUG) {
            if (is_wp_error($empleado_id)) {
                error_log('[DEBUG] wp_insert_post error: ' . $empleado_id->get_error_message());
            } else {
                error_log('[DEBUG] wp_insert_post result ID: ' . $empleado_id);
            }
        }
        if ($empleado_id) {
            update_post_meta($empleado_id, 'disponible', $disponible);
            update_post_meta($empleado_id, 'bar_id', $bar_id);
            update_post_meta($empleado_id, 'posicion_id', $posicion_id);
            update_post_meta($empleado_id, 'anio', $anio);

            error_log("DEBUG: Perfil de empleado creado correctamente con ID: $empleado_id");
            wp_send_json_success(['message' => 'Perfil de empleado creado correctamente.', 'empleado_id' => $empleado_id]);
        } else {
            error_log("ERROR: No se pudo crear el perfil de empleado.");
            wp_send_json_error(['message' => 'Error al crear el perfil.']);
        }
    }
    // ACTUALIZAR PERFIL DE EMPLEADO EXISTENTE.
    else {
        $empleado_post = get_post($empleado_id);
        if (!$empleado_post || $empleado_post->post_type !== 'empleado') {
            wp_send_json_error(['message' => 'Empleado no válido.']);
            wp_die();
        }
        if ($empleado_post->post_author != $current_user->ID) {
            wp_send_json_error(['message' => 'No tienes permisos para editar este empleado.']);
            wp_die();
        }

        wp_update_post(['ID' => $empleado_id, 'post_title' => $nombre]);
        update_post_meta($empleado_id, 'disponible', $disponible);

        error_log("DEBUG: Perfil de empleado actualizado correctamente con ID: $empleado_id");
        wp_send_json_success(['message' => 'Perfil de empleado actualizado correctamente.']);
    }
}

add_action('wp_ajax_cdb_form_empleado_submit', 'cdb_form_empleado_submit');
add_action('wp_ajax_nopriv_cdb_form_empleado_submit', 'cdb_form_empleado_submit');
