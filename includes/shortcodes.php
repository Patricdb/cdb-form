<?php
// Evitar acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit;
}

/*---------------------------------------------------------------
 * 1. FUNCIONES DE OBTENCIÓN DE DATOS
 *---------------------------------------------------------------*/

/**
 * Obtiene y almacena en caché la ID del empleado asociado a un usuario.
 *
 * Se busca el post de tipo 'empleado' publicado más reciente del usuario y se guarda en caché
 * durante 300 segundos para optimizar las consultas.
 *
 * @param int $user_id ID del usuario.
 * @return int|null ID del empleado o null si no existe.
 */
function cdb_obtener_empleado_id($user_id) {
    $cache_key = 'empleado_id_' . $user_id;
    $empleado_id = wp_cache_get($cache_key, 'cdb_form');

    if ($empleado_id === false) {
        global $wpdb;
        $empleado_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_type = 'empleado'
               AND post_author = %d
               AND post_status = 'publish'
             ORDER BY post_date DESC
             LIMIT 1",
            $user_id
        ));
        wp_cache_set($cache_key, $empleado_id, 'cdb_form', 300);
    }
    return $empleado_id;
}

/**
 * Calcula de forma dinámica la Puntuación de Experiencia para un empleado.
 *
 * Recorre todas las experiencias registradas en la tabla cdb_experiencia y suma el valor
 * de la posición obtenido desde el meta '_cdb_posiciones_score' y, en su caso, la puntuación de la zona
 * asignada al bar de cada experiencia.
 *
 * @param int $empleado_id ID del empleado.
 * @return int Puntuación total de experiencia.
 */
function cdb_calcular_puntuacion_experiencia_dinamica($empleado_id) {
    global $wpdb;
    $tabla_exp = $wpdb->prefix . 'cdb_experiencia';
    $total = 0;
    // Ahora se obtiene también el bar_id para poder extraer la puntuación de la zona.
    $experiencias = $wpdb->get_results(
        $wpdb->prepare("SELECT posicion_id, bar_id FROM {$tabla_exp} WHERE empleado_id = %d", $empleado_id)
    );
    if (!empty($experiencias)) {
        foreach ($experiencias as $exp) {
            // Obtener la puntuación de la posición.
            $score = get_post_meta($exp->posicion_id, '_cdb_posiciones_score', true);
            $score = intval($score);
            // Inicialmente, la puntuación de la zona es cero.
            $zone_score = 0;
            if (!empty($exp->bar_id)) {
                // Obtener el ID de la zona asignada al bar.
                $zona_id = get_post_meta($exp->bar_id, '_cdb_bar_zona_id', true);
                if ($zona_id) {
                    $zone_score = intval(get_post_meta($zona_id, 'puntuacion_zona', true));
                }
            }
            $total += ($score + $zone_score);
        }
    }
    return $total;
}

/*---------------------------------------------------------------
 * 2. SHORTCODE [cdb_bienvenida_usuario]
 *---------------------------------------------------------------
 * Muestra un saludo al usuario y carga secciones específicas según su rol.
 *---------------------------------------------------------------*/

/**
 * Shortcode [cdb_bienvenida_usuario]
 * Muestra un saludo personalizado y, según el rol, carga las secciones de empleado y/o empleador.
 */
function cdb_bienvenida_usuario_shortcode() {
    if (!is_user_logged_in()) {
        return '<p style="color: red;">' . esc_html__( 'Debes iniciar sesión para acceder a esta página.', 'cdb-form' ) . '</p>';
    }
    $current_user = wp_get_current_user();
    $output  = '<h1>' . sprintf( esc_html__( '¡Hola, %s!', 'cdb-form' ), esc_html($current_user->display_name) ) . '</h1>';
    $output .= '<p>' . esc_html__( 'Grácias por colaborar con el Proyecto CdB!', 'cdb-form' ) . '</p>';

    // Cargar la sección de empleado si el usuario tiene ese rol.
    if (in_array('empleado', (array) $current_user->roles)) {
        $output .= do_shortcode('[cdb_bienvenida_empleado]');
    }
    // Cargar la sección de empleador si el usuario tiene ese rol.
    if (in_array('empleador', (array) $current_user->roles)) {
        $output .= do_shortcode('[cdb_bienvenida_empleador]');
    }
    return $output;
}
add_shortcode('cdb_bienvenida_usuario', 'cdb_bienvenida_usuario_shortcode');

/*---------------------------------------------------------------
 * 3. SHORTCODE [cdb_bienvenida_empleado]
 *---------------------------------------------------------------
 * Muestra la información básica del empleado (perfil, disponibilidad y puntuaciones).
 *---------------------------------------------------------------*/

/**
 * Shortcode [cdb_bienvenida_empleado]
 * Muestra información del perfil del empleado, un formulario para actualizar la disponibilidad,
 * y las puntuaciones (gráfica y de experiencia).
 */
function cdb_bienvenida_empleado_shortcode() {
    if (!is_user_logged_in()) {
        return '';
    }
    $current_user = wp_get_current_user();
    // Solo procesar si el usuario tiene el rol 'empleado'.
    if (!in_array('empleado', (array) $current_user->roles)) {
        return '';
    }
    $empleado_id = cdb_obtener_empleado_id($current_user->ID);
    $output = '';

    if ($empleado_id) {
        $empleado_nombre  = get_the_title($empleado_id);
        $empleado_url     = get_permalink($empleado_id);
        $disponible       = get_post_meta($empleado_id, 'disponible', true);

        $output .= '<p><strong>' . esc_html__( 'Tu empleado:', 'cdb-form' ) . '</strong> <a href="' . esc_url($empleado_url) . '">' . esc_html($empleado_nombre) . '</a></p>';

        // Formulario para actualizar disponibilidad.
        $output .= '<form id="cdb-update-disponibilidad" method="post">
                        <label for="disponible">' . esc_html__( 'Actualizar Disponibilidad:', 'cdb-form' ) . '</label>
                        <select id="disponible" name="disponible">
                            <option value="1" ' . selected($disponible, 1, false) . '>' . esc_html__( 'Sí', 'cdb-form' ) . '</option>
                            <option value="0" ' . selected($disponible, 0, false) . '>' . esc_html__( 'No', 'cdb-form' ) . '</option>
                        </select>
                        <input type="hidden" name="empleado_id" value="' . esc_attr($empleado_id) . '">
                        <input type="hidden" name="security" value="' . wp_create_nonce('cdb_form_nonce') . '">
                        <button type="submit">' . esc_html__( 'Actualizar', 'cdb-form' ) . '</button>
                    </form>';

        // Obtener la puntuación gráfica y la de experiencia
        $puntuacion_total_meta = get_post_meta($empleado_id, 'cdb_puntuacion_total', true);
        $puntuacion_experiencia = get_post_meta($empleado_id, 'cdb_experiencia_score', true);
        $puntuacion_experiencia = intval($puntuacion_experiencia);

        // Sumamos la puntuación de experiencia (dividida entre 100) al total gráfico
        if (!empty($puntuacion_total_meta)) {
            $puntuacion_total_final = floatval($puntuacion_total_meta) + ($puntuacion_experiencia / 100);
            $puntuacion_total_final = round($puntuacion_total_final, 1); // Redondeamos a 1 decimal (opcional)
            $output .= cdb_generar_barra_progreso_simple($puntuacion_total_final);
        } else {
            $output .= '<p>' . esc_html__( 'Puntuación Gráfica no disponible.', 'cdb-form' ) . '</p>';
        }

        // Mostrar la Puntuación de Experiencia.
        $output .= '<p><strong>' . esc_html__( 'Puntuación de Experiencia:', 'cdb-form' ) . '</strong> ' . esc_html($puntuacion_experiencia) . '</p>';
    } else {
        $output .= '<p style="color: red;">' . esc_html__( 'No tienes ningún perfil de empleado asignado.', 'cdb-form' ) . '</p>';
        $output .= do_shortcode('[cdb_form_empleado]');
    }
    return $output;
}
add_shortcode('cdb_bienvenida_empleado', 'cdb_bienvenida_empleado_shortcode');

/**
 * Función para generar la barra de progreso simple con indicadores.
 *
 * Se muestra una barra de 0 a 100 con indicadores en:
 *  Nivel 0 (≤10, color negro),
 *  Nivel 1 (11-20, color negro),
 *  Nivel 1.1 (21-30, color cobre),
 *  Nivel 1.2 (31-40, color cobre),
 *  Nivel 2 (41-50, color plata),
 *  Nivel 2.1 (51-60, color plata),
 *  Nivel 3 (61-70, color oro),
 *  Nivel 3.1 (71-80, color oro) y
 *  Nivel 4 (81-100, color diamante).
 *
 * @param int|float $puntuacion_total Puntuación total (0-100)
 * @return string HTML generado
 */
function cdb_generar_barra_progreso_simple($puntuacion_total) {
    // Asegurarse de que la puntuación no supere 100.
    $puntuacion_total = floatval($puntuacion_total);
    if ($puntuacion_total > 100) {
        $puntuacion_total = 100;
    }
    
    ob_start();
    ?>
    <!-- Inline CSS para la barra de progreso y los indicadores -->
    <style>
      .cdb-progress-container {
          position: relative;
          width: 100%;
          height: 30px;
          background-color: #e0e0e0;
          border-radius: 5px;
          margin: 20px 0;
      }
      .cdb-progress-filled {
          height: 100%;
          background-color: #969696; /* Color fijo de la barra */
          width: 0;
          border-radius: 5px;
          transition: width 0.5s ease;
      }
      .cdb-progress-markers {
          position: absolute;
          top: -20px;
          left: 0;
          width: 100%;
      }
      .cdb-progress-marker {
          position: absolute;
          transform: translateX(-50%);
          font-size: 12px;
          font-weight: bold;
      }
    </style>

    <!-- Barra de progreso -->
    <div class="cdb-progress-container">
        <!-- Relleno proporcional a la puntuación total -->
        <div class="cdb-progress-filled" style="width: <?php echo intval($puntuacion_total); ?>%;"></div>
        <!-- Indicadores en la parte superior de la barra -->
        <div class="cdb-progress-markers">
            <div class="cdb-progress-marker" style="left: 5%; color: #c0c0c0;">Nivel</div>
            <div class="cdb-progress-marker" style="left: 11%; color: #c0c0c0;">0</div>
            <div class="cdb-progress-marker" style="left: 21%; color: #c0c0c0;">1</div>
            <div class="cdb-progress-marker" style="left: 31%; color: #c0c0c0;">1.1</div>
            <div class="cdb-progress-marker" style="left: 41%; color: #000;">2</div>
            <div class="cdb-progress-marker" style="left: 51%; color: #000;">2.1</div>
            <div class="cdb-progress-marker" style="left: 61%; color: #dbc63d;">3</div>
            <div class="cdb-progress-marker" style="left: 71%; color: #dbc63d;">3.1</div>
            <div class="cdb-progress-marker" style="left: 81%; color: #07ada8;">4</div>
        </div>
    </div>
    <!-- Mostrar la puntuación total -->
    <p><strong>Puntuación Total:</strong> <?php echo $puntuacion_total; ?>/100</p>
    <?php
    return ob_get_clean();
}

/*---------------------------------------------------------------
 * 4. SHORTCODE [cdb_experiencia]
 *---------------------------------------------------------------
 * Muestra el formulario de experiencia y la lista de experiencias.
 *---------------------------------------------------------------*/

/**
 * Shortcode [cdb_experiencia]
 * Incluye la plantilla que muestra el formulario y la lista de experiencias.
 */
function cdb_experiencia_shortcode() {
    if (!is_user_logged_in()) {
        return '<p style="color: red;">' . esc_html__( 'Debes iniciar sesión para acceder a esta página.', 'cdb-form' ) . '</p>';
    }
    $current_user = wp_get_current_user();
    if (!in_array('empleado', (array) $current_user->roles)) {
        return '';
    }
    $empleado_id = (int) cdb_obtener_empleado_id($current_user->ID);
    if ($empleado_id === 0) {
        return '<p style="color: red;">' . esc_html__( 'No tienes un perfil de empleado registrado.', 'cdb-form' ) . '</p>';
    }
    ob_start();
    include CDB_FORM_PATH . 'templates/form-experiencia-template.php';
    return ob_get_clean();
}
add_shortcode('cdb_experiencia', 'cdb_experiencia_shortcode');

/*---------------------------------------------------------------
 * 5. SHORTCODE [cdb_form_empleado]
 *---------------------------------------------------------------
 * Muestra el formulario para crear o editar el perfil de empleado.
 *---------------------------------------------------------------*/

/**
 * Shortcode [cdb_form_empleado]
 * Incluye la plantilla para el formulario de empleado.
 */
function cdb_form_empleado_shortcode() {
    ob_start();
    include CDB_FORM_PATH . 'templates/form-empleado-template.php';
    return ob_get_clean();
}
add_shortcode('cdb_form_empleado', 'cdb_form_empleado_shortcode');

/*---------------------------------------------------------------
 * 6. SHORTCODE [cdb_form_bar]
 *---------------------------------------------------------------
 * Muestra el formulario para el CPT "bar".
 *---------------------------------------------------------------*/

/**
 * Shortcode [cdb_form_bar]
 * Incluye la plantilla para el formulario de bar.
 */
function cdb_form_bar_shortcode() {
    ob_start();
    include CDB_FORM_PATH . 'templates/form-bar-template.php';
    return ob_get_clean();
}
add_shortcode('cdb_form_bar', 'cdb_form_bar_shortcode');

/*---------------------------------------------------------------
 * 7. SHORTCODE [cdb_puntuacion_total]
 *---------------------------------------------------------------
 * Muestra la Puntuación Total (meta 'cdb_puntuacion_total') del empleado para la gráfica.
 *---------------------------------------------------------------*/

/**
 * Shortcode [cdb_puntuacion_total]
 * Muestra la puntuación gráfica del empleado.
 */
function cdb_mostrar_puntuacion_total() {
    if (!is_user_logged_in()) {
        return '<p>' . esc_html__( 'Error: Debes iniciar sesión para ver tu puntuación.', 'cdb-form' ) . '</p>';
    }
    $current_user = wp_get_current_user();
    if (!in_array('empleado', (array) $current_user->roles)) {
        return '';
    }
    $empleado_id = cdb_obtener_empleado_id($current_user->ID);
    if (!$empleado_id) {
        return '<p>' . esc_html__( 'No se encontró un empleado asociado a este usuario.', 'cdb-form' ) . '</p>';
    }
    $puntuacion_total = get_post_meta($empleado_id, 'cdb_puntuacion_total', true);
    if (!$puntuacion_total) {
        return '<p>' . esc_html__( 'Puntuación Gráfica no disponible.', 'cdb-form' ) . '</p>';
    }
    return '<p><strong>' . esc_html__( 'Puntuación Gráfica:', 'cdb-form' ) . '</strong> ' . esc_html($puntuacion_total) . '</p>';
}
add_shortcode('cdb_puntuacion_total', 'cdb_mostrar_puntuacion_total');

/**
 * Shortcode [cdb_top_empleados_experiencia_precalculada]
 *
 * Muestra una tabla con el ranking de los 21 empleados según la meta 'cdb_experiencia_score'.
 * Incluye un selector para filtrar por disponibilidad (meta 'disponible' = '1'),
 * visible solo para el rol 'administrator'.
 */

function cdb_top_empleados_experiencia_precalculada_shortcode() {
    // 1) Determinar si el usuario es administrador
    $usuario_es_admin = current_user_can('administrator');

    // 2) Ver si en la URL está ?disponible=1, solo aplicable si es admin
    $filtrar_disponibles = false;
    if ($usuario_es_admin && isset($_GET['disponible']) && $_GET['disponible'] === '1') {
        $filtrar_disponibles = true;
    }

    // 3) Construir la meta_query si se filtra por disponibilidad
    $meta_query = [];
    if ($filtrar_disponibles) {
        $meta_query[] = [
            'key'     => 'disponible',
            'value'   => '1',
            'compare' => '=',
        ];
    }

    // 4) Preparar la WP_Query, ordenando por cdb_experiencia_score desc
    $args = [
        'post_type'      => 'empleado',
        'post_status'    => 'publish',
        'meta_key'       => 'cdb_experiencia_score',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'posts_per_page' => 21,
        'meta_query'     => $meta_query,
    ];

    $query = new WP_Query($args);

    // 5) Si no hay empleados, avisar
    if (!$query->have_posts()) {
        return '<p>' . esc_html__( 'No se encontraron empleados.', 'cdb-form' ) . '</p>';
    }

    // 6) Generar la tabla
    $output  = '<h3>Top 21 Empleados por Puntuación de Experiencia</h3>';
    $output .= '<table style="width:100%; border-collapse: collapse;">';
    $output .= '<thead>';
    $output .= '  <tr>';
    $output .= '    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">#</th>';
    $output .= '    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Empleado</th>';
    $output .= '    <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Puntuación</th>';
    $output .= '  </tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    $posicion = 1;
    while ($query->have_posts()) {
        $query->the_post();
        $score = get_post_meta(get_the_ID(), 'cdb_experiencia_score', true);

        $output .= '<tr>';
        $output .= '  <td style="border: 1px solid #ddd; padding: 8px;">' . $posicion . '</td>';
        $output .= '  <td style="border: 1px solid #ddd; padding: 8px;">';
        $output .= '    <a href="' . esc_url(get_permalink()) . '">';
        $output .=          esc_html(get_the_title());
        $output .= '    </a>';
        $output .= '  </td>';
        $output .= '  <td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($score) . '</td>';
        $output .= '</tr>';

        $posicion++;
    }

    wp_reset_postdata();
    $output .= '</tbody></table>';

    // 7) Mostrar formulario para filtrar disponibles SOLO si es admin
    if ($usuario_es_admin) {
        $output .= '<form method="get" style="margin-top: 1em;">';
        $output .= '    <label for="disponible">Mostrar solo disponibles:</label> ';
        $output .= '    <select name="disponible" onchange="this.form.submit()">';
        // <option value="0"> => no filtrar, <option value="1"> => filtrar
        $output .= '        <option value="0" ' . selected($filtrar_disponibles, false, false) . '>No</option>';
        $output .= '        <option value="1" ' . selected($filtrar_disponibles, true, false) . '>Sí</option>';
        $output .= '    </select>';
        $output .= '</form>';
    }

    return $output;
}
add_shortcode('cdb_top_empleados_experiencia_precalculada', 'cdb_top_empleados_experiencia_precalculada_shortcode');

/**
 * Shortcode [cdb_top_empleados_puntuacion_total]
 *
 * Muestra una tabla con el ranking de los 21 empleados según su meta 'cdb_puntuacion_total'.
 * Se incluye la opción de filtrar por disponibilidad (meta 'disponible' = '1'),
 * pero solo se muestra este selector si el usuario actual es 'administrator'.
 *
 * Uso habitual: [cdb_top_empleados_puntuacion_total]
 */

function cdb_top_empleados_puntuacion_total_shortcode() {
    // 1) Determinar si el usuario actual es administrador.
    //    Solo en ese caso, mostraremos el selector de disponibilidad.
    $usuario_es_admin = current_user_can('administrator');

    // 2) Tomar de GET si se quiere filtrar ?disponible=1
    //    y aplicar el filtro solo si es admin.
    $filtrar_disponibles = false;
    if ($usuario_es_admin && isset($_GET['disponible']) && $_GET['disponible'] === '1') {
        $filtrar_disponibles = true;
    }

    // 3) Construir la meta_query si se filtra por disponibilidad.
    $meta_query = [];
    if ($filtrar_disponibles) {
        $meta_query[] = [
            'key'     => 'disponible',
            'value'   => '1',
            'compare' => '=',
        ];
    }

    // 4) Preparar la consulta WP_Query.
    //    Ordenamos por 'cdb_puntuacion_total' desc y mostramos 21 resultados.
    $args = [
        'post_type'      => 'empleado',
        'post_status'    => 'publish',
        'meta_key'       => 'cdb_puntuacion_total',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'posts_per_page' => 21,
        'meta_query'     => $meta_query,
    ];

    $query = new WP_Query($args);

    // 5) Si no hay empleados, salimos.
    if (!$query->have_posts()) {
        return '<p>' . esc_html__( 'No se encontraron empleados con puntuación total.', 'cdb-form' ) . '</p>';
    }

    // 6) Cabecera de la tabla
    $output  = '<h3>Top 21 Empleados por Puntuación Total (Gráfica)</h3>';
    $output .= '<table style="width:100%; border-collapse: collapse;">';
    $output .= '<thead>';
    $output .= '<tr>';
    // Columna para la posición en el ranking.
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">#</th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Empleado</th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Puntuación</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    // 7) Mostrar cada empleado, con su ranking en la tabla
    $posicion = 1;
    while ($query->have_posts()) {
        $query->the_post();
        $puntuacion_total = get_post_meta(get_the_ID(), 'cdb_puntuacion_total', true);

        $output .= '<tr>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $posicion . '</td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">';
        $output .= '<a href="' . esc_url(get_permalink()) . '">';
        $output .= esc_html(get_the_title());
        $output .= '</a></td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($puntuacion_total) . '</td>';
        $output .= '</tr>';

        $posicion++;
    }
    wp_reset_postdata();

    $output .= '</tbody></table>';

    // 8) Mostrar el formulario de filtrado SOLO si es admin
    if ($usuario_es_admin) {
        // Mantenemos el valor 'disponible' en el select: 0 => No, 1 => Sí
        $output .= '<form method="get" style="margin-top: 1em;">';
        $output .= '    <label for="disponible">Mostrar solo disponibles:</label> ';
        $output .= '    <select name="disponible" onchange="this.form.submit()">';
        $output .= '        <option value="0" ' . selected($filtrar_disponibles, false, false) . '>No</option>';
        $output .= '        <option value="1" ' . selected($filtrar_disponibles, true, false) . '>Sí</option>';
        $output .= '    </select>';
        $output .= '</form>';
    }

    return $output;
}
add_shortcode('cdb_top_empleados_puntuacion_total', 'cdb_top_empleados_puntuacion_total_shortcode');

/**
 * Shortcode [cdb_posiciones_empleados]
 *
 * Muestra Empleados relacionados a una Posición (tabla cdb_experiencia),
 * ordenados por:
 *  - Año (desc)
 *  - Puntuación Gráfica (desc)
 * Evita empleados repetidos, mostrando solo el año más reciente,
 * y limita a 21 resultados. Además, incluye la opción de filtrar
 * por disponibilidad (meta 'disponible' = '1'), pero ese filtro
 * solo se mostrará si el usuario es 'administrator'.
 *
 * Uso del shortcode: [cdb_posiciones_empleados posicion_id="123"]
 *
 * Nota: Para cambiar el rol que puede ver el selector, busca la parte de
 * 'current_user_can' y actualiza a las capacidades o roles que necesites.
 */
function cdb_posiciones_empleados_shortcode($atts) {
    global $wpdb;
    ob_start();

    // 1) Determinar la posición (posicion_id) priorizando la query param ?posicion_id=...
    $posicion_id_from_get  = isset($_GET['posicion_id']) ? (int) $_GET['posicion_id'] : 0;
    $posicion_id_shortcode = isset($atts['posicion_id']) ? (int) $atts['posicion_id'] : (int) get_the_ID();
    $posicion_id = $posicion_id_from_get ?: $posicion_id_shortcode;

    // Validación: si no hay posicion_id válido, salimos
    if (!$posicion_id) {
        return '<p style="color: red;">Error: No se ha proporcionado una posición válida.</p>';
    }

    // 2) Recuperar el nombre (título) de la Posición
    $posicion_title = get_the_title($posicion_id);
    if (empty($posicion_title)) {
        $posicion_title = 'Desconocida';
    }

    // 3) Determinar si se filtra por disponibilidad
    //    (solo se aplica si se pasa disponible=1, sea por GET o shortcode)
    $disponible_from_get    = (isset($_GET['disponible']) && $_GET['disponible'] === '1');
    $disponible_from_shortcode = (isset($atts['disponible']) && $atts['disponible'] === '1');
    $filtrar_disponibles   = $disponible_from_get || $disponible_from_shortcode;

    // 4) Consultar la tabla cdb_experiencia para esta posicion
    $tabla_exp = $wpdb->prefix . 'cdb_experiencia';
    $sql = $wpdb->prepare("
        SELECT empleado_id, anio
        FROM $tabla_exp
        WHERE posicion_id = %d
        ORDER BY anio DESC
    ", $posicion_id);
    $rows = $wpdb->get_results($sql);

    // Almacenaremos anio, puntuacion, etc. para ordenarlos luego
    $datos = [];

    if (!empty($rows)) {
        foreach ($rows as $row) {
            $empleado_id = (int) $row->empleado_id;
            $anio        = (int) $row->anio;

            // Si 'filtrar_disponibles' es true, comprobar la meta 'disponible'
            if ($filtrar_disponibles) {
                $dispo = get_post_meta($empleado_id, 'disponible', true);
                if ($dispo !== '1') {
                    continue;
                }
            }

            // cdb_puntuacion_total => puntuación gráfica
            $puntuacion = floatval(get_post_meta($empleado_id, 'cdb_puntuacion_total', true));
            // Formatear con un decimal (ej. 2 => 2.0)
            $puntuacion_formateada = number_format($puntuacion, 1, '.', '');

            $datos[] = [
                'empleado_id'  => $empleado_id,
                'anio'         => $anio,
                'puntuacion'   => $puntuacion,
                'puntuacion_f' => $puntuacion_formateada,
            ];
        }
    }

    // 5) Evitar repeticiones: solo conservar el año máximo por empleado
    $unicos_por_empleado = [];
    foreach ($datos as $item) {
        $eid = $item['empleado_id'];
        if (!isset($unicos_por_empleado[$eid])) {
            $unicos_por_empleado[$eid] = $item;
        } else {
            if ($item['anio'] > $unicos_por_empleado[$eid]['anio']) {
                $unicos_por_empleado[$eid] = $item;
            }
        }
    }

    // Pasarlo a array indexado
    $datos_filtrados = array_values($unicos_por_empleado);

    // 6) Ordenar: primero por año desc, luego por puntuación desc
    usort($datos_filtrados, function($a, $b) {
        // Año desc
        if ($b['anio'] !== $a['anio']) {
            return $b['anio'] - $a['anio'];
        }
        // Puntuación desc
        return $b['puntuacion'] <=> $a['puntuacion'];
    });

    // 7) Tomar los primeros 21
    $datos_top = array_slice($datos_filtrados, 0, 21);

    // 8) Encabezado y tabla
    echo '<h2>Top 21 Empleados en la posición ' . esc_html($posicion_title) . '</h2>';

    echo '<table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align:left; border-bottom: 1px solid #ccc; padding: 6px;">Año</th>
                    <th style="text-align:left; border-bottom: 1px solid #ccc; padding: 6px;">Empleado</th>
                    <th style="text-align:left; border-bottom: 1px solid #ccc; padding: 6px;">Puntuación Gráfica</th>
                </tr>
            </thead>
            <tbody>';

    if (!empty($datos_top)) {
        foreach ($datos_top as $item) {
            $eid = $item['empleado_id'];
            $anio = $item['anio'];
            $pf = $item['puntuacion_f'];

            // Obtener título y enlace del post empleado
            $nombre_empleado = get_the_title($eid);
            $url_empleado = get_permalink($eid);

            echo '<tr>
                    <td style="padding: 6px;">' . esc_html($anio) . '</td>
                    <td style="padding: 6px;">
                        <a href="' . esc_url($url_empleado) . '" style="text-decoration:none;">'
                            . esc_html($nombre_empleado) .
                        '</a>
                    </td>
                    <td style="padding: 6px;">' . esc_html($pf) . '</td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="3" style="padding: 6px;">No hay empleados registrados para esta posición.</td></tr>';
    }

    echo '  </tbody>
          </table>';

    /**
     * 9) Mostrar selector para filtrar Disponibilidad
     *    SOLO si el usuario es administrator.
     *
     *    Para dar acceso a más roles en el futuro, reemplaza 'administrator'
     *    con la capacidad o role check que necesites, p.ej.:
     *      current_user_can('editor')
     *      current_user_can('manage_options')
     *    etc.
     */
    if (current_user_can('administrator')) {
        echo '<form method="get" style="margin-top: 1em;">';
        echo '    <input type="hidden" name="posicion_id" value="' . esc_attr($posicion_id) . '"/>';
        echo '    <label for="disponible">Mostrar solo disponibles:</label> ';
        echo '    <select name="disponible" onchange="this.form.submit()">';
        echo '        <option value="0" ' . selected($filtrar_disponibles, false, false) . '>No</option>';
        echo '        <option value="1" ' . selected($filtrar_disponibles, true, false) . '>Sí</option>';
        echo '    </select>';
        echo '</form>';
    }

    // Limpieza
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('cdb_posiciones_empleados', 'cdb_posiciones_empleados_shortcode');

/**
 * Shortcode [cdb_top_bares_puntuacion_total]
 * Muestra una tabla con el ranking de los 21 bares según su puntuación total (cdb_puntuacion_total).
 * Se muestra la posición en el ranking, el nombre del bar (con enlace a su perfil)
 * y la puntuación total.
 */
function cdb_top_bares_puntuacion_total_shortcode() {
    $args = array(
        'post_type'      => 'bar',
        'post_status'    => 'publish',
        'meta_key'       => 'cdb_puntuacion_total',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'posts_per_page' => 21,
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No se encontraron bares con puntuación total.</p>';
    }

    $output  = '<h3>Top 21 Bares por Puntuación Total (Gráfica)</h3>';
    $output .= '<table style="width:100%; border-collapse: collapse;">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;"></th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Bar</th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Puntuación</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    $posicion = 1;
    while ($query->have_posts()) {
        $query->the_post();
        $puntuacion_total = get_post_meta(get_the_ID(), 'cdb_puntuacion_total', true);

        $output .= '<tr>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $posicion . '</td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">';
        $output .= '<a href="' . esc_url(get_permalink()) . '">';
        $output .= esc_html(get_the_title());
        $output .= '</a></td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($puntuacion_total) . '</td>';
        $output .= '</tr>';

        $posicion++;
    }
    wp_reset_postdata();

    $output .= '</tbody>';
    $output .= '</table>';
    return $output;
}
add_shortcode('cdb_top_bares_puntuacion_total', 'cdb_top_bares_puntuacion_total_shortcode');

/**
 * Shortcode [cdb_top_bares_gmaps]
 * Muestra una tabla con el ranking de los 21 bares según su reputación "gmaps".
 * Se muestra la posición en el ranking, el nombre del bar (con enlace a su perfil)
 * y la reputación (campo "gmaps").
 */
function cdb_top_bares_gmaps_shortcode() {
    $args = array(
        'post_type'      => 'bar',
        'post_status'    => 'publish',
        'meta_key'       => 'gmaps',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'posts_per_page' => 21,
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No se encontraron bares con reputación (gmaps).</p>';
    }

    $output  = '<h3>Top 21 Bares por valoración en Google Maps</h3>';
    $output .= '<table style="width:100%; border-collapse: collapse;">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;"></th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Bar</th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Valoración</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    $posicion = 1;
    while ($query->have_posts()) {
        $query->the_post();
        $gmaps_rating = get_post_meta(get_the_ID(), 'gmaps', true);

        $output .= '<tr>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $posicion . '</td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">';
        $output .= '<a href="' . esc_url(get_permalink()) . '">';
        $output .= esc_html(get_the_title());
        $output .= '</a></td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($gmaps_rating) . '</td>';
        $output .= '</tr>';

        $posicion++;
    }
    wp_reset_postdata();

    $output .= '</tbody>';
    $output .= '</table>';
    return $output;
}
add_shortcode('cdb_top_bares_gmaps', 'cdb_top_bares_gmaps_shortcode');

/**
 * Shortcode [cdb_top_bares_tripadvisor]
 * Muestra una tabla con el ranking de los 21 bares según su reputación "tripadvisor".
 * Se muestra la posición en el ranking, el nombre del bar (con enlace a su perfil)
 * y la reputación (campo "tripadvisor").
 */
function cdb_top_bares_tripadvisor_shortcode() {
    $args = array(
        'post_type'      => 'bar',
        'post_status'    => 'publish',
        'meta_key'       => 'tripadvisor',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'posts_per_page' => 21,
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No se encontraron bares con reputación (tripadvisor).</p>';
    }

    $output  = '<h3>Top 21 Bares por valoración en TripAdvisor</h3>';
    $output .= '<table style="width:100%; border-collapse: collapse;">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;"></th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Bar</th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Valoración</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    $posicion = 1;
    while ($query->have_posts()) {
        $query->the_post();
        $tripadvisor_rating = get_post_meta(get_the_ID(), 'tripadvisor', true);

        $output .= '<tr>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $posicion . '</td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">';
        $output .= '<a href="' . esc_url(get_permalink()) . '">';
        $output .= esc_html(get_the_title());
        $output .= '</a></td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($tripadvisor_rating) . '</td>';
        $output .= '</tr>';

        $posicion++;
    }
    wp_reset_postdata();

    $output .= '</tbody>';
    $output .= '</table>';
    return $output;
}
add_shortcode('cdb_top_bares_tripadvisor', 'cdb_top_bares_tripadvisor_shortcode');

/**
 * Shortcode [cdb_top_bares_instagram]
 * Muestra una tabla con el ranking de los 21 bares según su reputación "instagram".
 * Se muestra la posición en el ranking, el nombre del bar (con enlace a su perfil)
 * y la reputación (campo "instagram").
 */
function cdb_top_bares_instagram_shortcode() {
    $args = array(
        'post_type'      => 'bar',
        'post_status'    => 'publish',
        'meta_key'       => 'instagram',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'posts_per_page' => 21,
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return '<p>No se encontraron bares con reputación (instagram).</p>';
    }

    $output  = '<h3>Top 21 Bares por seguidores en Instagram</h3>';
    $output .= '<table style="width:100%; border-collapse: collapse;">';
    $output .= '<thead>';
    $output .= '<tr>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;"></th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Bar</th>';
    $output .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Valoración</th>';
    $output .= '</tr>';
    $output .= '</thead>';
    $output .= '<tbody>';

    $posicion = 1;
    while ($query->have_posts()) {
        $query->the_post();
        $instagram_rating = get_post_meta(get_the_ID(), 'instagram', true);

        $output .= '<tr>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $posicion . '</td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">';
        $output .= '<a href="' . esc_url(get_permalink()) . '">';
        $output .= esc_html(get_the_title());
        $output .= '</a></td>';
        $output .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($instagram_rating) . '</td>';
        $output .= '</tr>';

        $posicion++;
    }
    wp_reset_postdata();

    $output .= '</tbody>';
    $output .= '</table>';
    return $output;
}
add_shortcode('cdb_top_bares_instagram', 'cdb_top_bares_instagram_shortcode');


/*---------------------------------------------------------------
 * 8. SHORTCODE [cdb_busqueda_empleados]
 *---------------------------------------------------------------
 * Muestra un listado/buscador avanzado de empleados filtrando por
 * nombre, equipo, posición, bar, año y disponibilidad.
 *---------------------------------------------------------------*/

/**
 * Shortcode [cdb_busqueda_empleados]
 *
 * Uso de ejemplo:
 *   [cdb_busqueda_empleados nombre="Ana" equipo_id="3" bar_id="4" anio="2024" disponible="1"]
 * También se puede usar mediante parámetros GET, por ejemplo:
 *   /pagina/?nombre=Ana&equipo_id=3
 */
function cdb_busqueda_empleados_shortcode($atts = array()) {
    global $wpdb;

    // ---------------------------
    // 1. Captura de parámetros
    // ---------------------------
    $atts = shortcode_atts(array(
        'nombre'      => '',
        'equipo_id'   => '',
        'posicion_id' => '',
        'bar_id'      => '',
        'anio'        => '',
        'disponible'  => '',
    ), $atts, 'cdb_busqueda_empleados');

    // Priorizar atributos sobre parámetros GET
    $nombre      = $atts['nombre'] !== ''      ? $atts['nombre']      : (isset($_GET['nombre'])      ? sanitize_text_field($_GET['nombre']) : '');
    $equipo_id   = $atts['equipo_id'] !== ''   ? intval($atts['equipo_id'])   : (isset($_GET['equipo_id'])   ? intval($_GET['equipo_id'])   : 0);
    $posicion_id = $atts['posicion_id'] !== '' ? intval($atts['posicion_id']) : (isset($_GET['posicion_id']) ? intval($_GET['posicion_id']) : 0);
    $bar_id      = $atts['bar_id'] !== ''      ? intval($atts['bar_id'])      : (isset($_GET['bar_id'])      ? intval($_GET['bar_id'])      : 0);
    $anio        = $atts['anio'] !== ''        ? intval($atts['anio'])        : (isset($_GET['anio'])        ? intval($_GET['anio'])        : 0);
    $disponible  = $atts['disponible'] !== ''  ? $atts['disponible']           : (isset($_GET['disponible'])  ? $_GET['disponible']          : '');

    // ---------------------------
    // 2. Construcción de la consulta SQL
    // ---------------------------
    $tabla_exp = $wpdb->prefix . 'cdb_experiencia';
    $posts     = $wpdb->posts;
    $postmeta  = $wpdb->postmeta;

    $sql = "SELECT exp.empleado_id, exp.anio, exp.bar_id, exp.posicion_id, exp.equipo_id,
                   e.post_title AS empleado_nombre,
                   bar.post_title AS bar_nombre,
                   pos.post_title AS posicion_nombre,
                   eq.post_title AS equipo_nombre,
                   score.meta_value AS puntuacion_total,
                   dispo.meta_value AS disponible
            FROM {$tabla_exp} exp
            JOIN {$posts} e   ON exp.empleado_id = e.ID AND e.post_type = 'empleado' AND e.post_status = 'publish'
            LEFT JOIN {$posts} bar ON exp.bar_id = bar.ID AND bar.post_type = 'bar' AND bar.post_status = 'publish'
            LEFT JOIN {$posts} pos ON exp.posicion_id = pos.ID AND pos.post_type = 'cdb_posiciones' AND pos.post_status = 'publish'
            LEFT JOIN {$posts} eq  ON exp.equipo_id = eq.ID AND eq.post_type = 'equipo' AND eq.post_status = 'publish'
            LEFT JOIN {$postmeta} score ON score.post_id = e.ID AND score.meta_key = 'cdb_puntuacion_total'
            LEFT JOIN {$postmeta} dispo ON dispo.post_id = e.ID AND dispo.meta_key = 'disponible'
            WHERE 1=1";

    $prepare_params = array();

    if ($nombre !== '') {
        $like = '%' . $wpdb->esc_like($nombre) . '%';
        $sql .= " AND e.post_title LIKE %s";
        $prepare_params[] = $like;
    }
    if ($equipo_id) {
        $sql .= " AND exp.equipo_id = %d";
        $prepare_params[] = $equipo_id;
    }
    if ($posicion_id) {
        $sql .= " AND exp.posicion_id = %d";
        $prepare_params[] = $posicion_id;
    }
    if ($bar_id) {
        $sql .= " AND exp.bar_id = %d";
        $prepare_params[] = $bar_id;
    }
    if ($anio) {
        $sql .= " AND exp.anio = %d";
        $prepare_params[] = $anio;
    }
    if ($disponible !== '') {
        $sql .= " AND dispo.meta_value = %s";
        $prepare_params[] = $disponible;
    }

    $sql .= " ORDER BY exp.anio DESC, CAST(score.meta_value AS DECIMAL(10,2)) DESC";

    // Limitar resultados iniciales para filtrar después
    $sql .= " LIMIT 100";

    $query = $wpdb->prepare($sql, $prepare_params);
    $rows  = $wpdb->get_results($query);

    // ---------------------------
    // 3. Procesamiento de resultados
    // ---------------------------
    $empleados = array();
    if (!empty($rows)) {
        foreach ($rows as $row) {
            $eid = intval($row->empleado_id);
            if (isset($empleados[$eid])) {
                continue; // Ya tenemos el más reciente por orden
            }
            $empleados[$eid] = array(
                'empleado_id' => $eid,
                'nombre'      => $row->empleado_nombre,
                'url'         => get_permalink($eid),
                'posicion'    => $row->posicion_nombre,
                'bar'         => $row->bar_nombre,
                'equipo'      => $row->equipo_nombre,
                'anio'        => intval($row->anio),
                'puntuacion'  => $row->puntuacion_total !== null ? number_format((float)$row->puntuacion_total, 1, '.', '') : '0.0',
                'disponible'  => ($row->disponible === '1') ? __( 'Disponible', 'cdb-form' ) : __( 'No Disponible', 'cdb-form' ),
            );
            if (count($empleados) >= 21) {
                break;
            }
        }
    }

    // ---------------------------
    // 4. Generación del HTML
    // ---------------------------
    ob_start();
    if (empty($empleados)) {
        echo '<p>' . esc_html__( 'No se encontraron empleados con esos filtros.', 'cdb-form' ) . '</p>';
    } else {
        echo '<table style="width:100%; border-collapse: collapse;">';
        echo '<thead><tr>';
        echo '<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">' . esc_html__( 'Año', 'cdb-form' ) . '</th>';
        echo '<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">' . esc_html__( 'Empleado', 'cdb-form' ) . '</th>';
        echo '<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">' . esc_html__( 'Posición', 'cdb-form' ) . '</th>';
        echo '<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">' . esc_html__( 'Bar', 'cdb-form' ) . '</th>';
        echo '<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">' . esc_html__( 'Equipo', 'cdb-form' ) . '</th>';
        echo '<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">' . esc_html__( 'Puntuación', 'cdb-form' ) . '</th>';
        echo '<th style="text-align:left; padding:6px; border-bottom:1px solid #ccc;">' . esc_html__( 'Disponibilidad', 'cdb-form' ) . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($empleados as $emp) {
            echo '<tr>';
            echo '<td style="padding:6px;">' . esc_html($emp['anio']) . '</td>';
            echo '<td style="padding:6px;"><a href="' . esc_url($emp['url']) . '" style="text-decoration:none;">' . esc_html($emp['nombre']) . '</a></td>';
            echo '<td style="padding:6px;">' . esc_html($emp['posicion']) . '</td>';
            echo '<td style="padding:6px;">' . esc_html($emp['bar']) . '</td>';
            echo '<td style="padding:6px;">' . esc_html($emp['equipo']) . '</td>';
            echo '<td style="padding:6px;">' . esc_html($emp['puntuacion']) . '</td>';
            echo '<td style="padding:6px;">' . esc_html($emp['disponible']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    return ob_get_clean();
}
add_shortcode('cdb_busqueda_empleados', 'cdb_busqueda_empleados_shortcode');

