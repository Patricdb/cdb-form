<?php
// Evitar acceso directo al archivo.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registro de Custom Post Types en CdB_Form.
 *
 * Este archivo se encarga de registrar los tipos de contenido personalizados (CPTs)
 * utilizados en el plugin CdB_Form. Se pueden agregar más CPTs en el futuro según se requiera.
 */

/**
 * Registra el Custom Post Type 'cdb_posiciones'.
 *
 * Este CPT se utiliza para definir las posiciones laborales y almacenar el valor
 * de puntuación asociado, que se usará en la suma de la puntuación de experiencia.
 */
function cdb_register_cpt_posiciones() {
    $labels = array(
        'name'               => __( 'Posiciones', 'cdb-form' ),
        'singular_name'      => __( 'Posición', 'cdb-form' ),
        'menu_name'          => __( 'Posiciones', 'cdb-form' ),
        'name_admin_bar'     => __( 'Posición', 'cdb-form' ),
        'add_new'            => __( 'Añadir Nueva', 'cdb-form' ),
        'add_new_item'       => __( 'Añadir Nueva Posición', 'cdb-form' ),
        'new_item'           => __( 'Nueva Posición', 'cdb-form' ),
        'edit_item'          => __( 'Editar Posición', 'cdb-form' ),
        'view_item'          => __( 'Ver Posición', 'cdb-form' ),
        'all_items'          => __( 'Todas las Posiciones', 'cdb-form' ),
        'search_items'       => __( 'Buscar Posiciones', 'cdb-form' ),
        'not_found'          => __( 'No se encontraron posiciones', 'cdb-form' ),
        'not_found_in_trash' => __( 'No se encontraron posiciones en la papelera', 'cdb-form' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'posiciones'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'       => true,
    );

    register_post_type('cdb_posiciones', $args);
}
add_action('init', 'cdb_register_cpt_posiciones');

/*---------------------------------------------------------------
 * META BOX PARA EL VALOR DE PUNTUACIÓN EN EL CPT 'cdb_posiciones'
 *---------------------------------------------------------------*/

/**
 * Agrega un meta box en el editor de 'cdb_posiciones' para definir el valor de puntuación.
 */
function cdb_add_meta_box_posicion_score() {
    add_meta_box(
        'cdb_posiciones_score',                 // ID del meta box.
        __( 'Valor de Puntuación', 'cdb-form' ), // Título que se muestra.
        'cdb_render_posiciones_score_meta_box', // Función que renderiza el contenido.
        'cdb_posiciones',                       // CPT donde se agrega.
        'side',                                 // Ubicación (side, normal, advanced).
        'default'                               // Prioridad.
    );
}
add_action('add_meta_boxes', 'cdb_add_meta_box_posicion_score');

/**
 * Renderiza el meta box para el valor de puntuación en el CPT 'cdb_posiciones'.
 *
 * Se utiliza un nonce para la seguridad y se muestra un campo numérico.
 *
 * @param WP_Post $post Objeto del post actual.
 */
function cdb_render_posiciones_score_meta_box($post) {
    // Generar un nonce para verificar la seguridad en el guardado.
    wp_nonce_field('cdb_save_posiciones_score', 'cdb_posiciones_score_nonce');

    // Obtener el valor actual de la puntuación (si existe).
    $score_value = get_post_meta($post->ID, '_cdb_posiciones_score', true);
    ?>
    <label for="cdb_posiciones_score_field"><?php esc_html_e( 'Valor de Puntuación:', 'cdb-form' ); ?></label>
    <input 
        type="number" 
        id="cdb_posiciones_score_field" 
        name="cdb_posiciones_score_field" 
        value="<?php echo esc_attr($score_value); ?>" 
        step="1" 
        min="0" 
    />
    <?php
}

/**
 * Guarda el valor del meta box de puntuación cuando se guarda o actualiza un post de tipo 'cdb_posiciones'.
 *
 * Se valida el nonce, se evita el autosave y se comprueban los permisos antes de actualizar el meta.
 *
 * @param int $post_id ID del post.
 */
function cdb_save_posiciones_score_meta_box($post_id) {
    // Verificar nonce.
    if (!isset($_POST['cdb_posiciones_score_nonce']) || !wp_verify_nonce($_POST['cdb_posiciones_score_nonce'], 'cdb_save_posiciones_score')) {
        return;
    }

    // Evitar guardar durante un autosave.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Comprobar permisos para el CPT 'cdb_posiciones'.
    $post_type_object = get_post_type_object('cdb_posiciones');
    if (!$post_type_object || !current_user_can($post_type_object->cap->edit_post, $post_id)) {
        return;
    }

    // Si el campo está definido, actualizar el meta '_cdb_posiciones_score'.
    if (isset($_POST['cdb_posiciones_score_field'])) {
        $score = intval($_POST['cdb_posiciones_score_field']);
        update_post_meta($post_id, '_cdb_posiciones_score', $score);
    }
}
// Antes: add_action('save_post', 'cdb_save_posiciones_score_meta_box');
// Sustituimos por el hook específico del CPT:
add_action('save_post_cdb_posiciones', 'cdb_save_posiciones_score_meta_box');

/*---------------------------------------------------------------
 * COLUMNAS PERSONALIZADAS Y ORDENACIÓN POR PUNTUACIÓN
 *---------------------------------------------------------------*/

/**
 * Agrega una columna personalizada para mostrar la puntuación en la lista de posiciones.
 */
function cdb_add_posiciones_custom_column($columns) {
    $columns['cdb_posiciones_score'] = __( 'Puntuación', 'cdb-form' );
    return $columns;
}
add_filter('manage_cdb_posiciones_posts_columns', 'cdb_add_posiciones_custom_column');

/**
 * Muestra el valor de puntuación en la columna personalizada en la lista de posiciones.
 */
function cdb_show_posiciones_custom_column($column, $post_id) {
    if ($column === 'cdb_posiciones_score') {
        $score = get_post_meta($post_id, '_cdb_posiciones_score', true);
        echo $score ? esc_html($score) : '—';
    }
}
add_action('manage_cdb_posiciones_posts_custom_column', 'cdb_show_posiciones_custom_column', 10, 2);

/**
 * Hace que la columna de puntuación sea ordenable.
 */
function cdb_make_posiciones_score_column_sortable($columns) {
    $columns['cdb_posiciones_score'] = 'cdb_posiciones_score';
    return $columns;
}
add_filter('manage_edit-cdb_posiciones_sortable_columns', 'cdb_make_posiciones_score_column_sortable');

/**
 * Modifica la consulta para ordenar por puntuación en el listado de posiciones.
 */
function cdb_sort_posiciones_by_score($query) {
    if (!is_admin()) return;

    $orderby = $query->get('orderby');
    if ($orderby === 'cdb_posiciones_score') {
        $query->set('meta_key', '_cdb_posiciones_score');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'cdb_sort_posiciones_by_score');
