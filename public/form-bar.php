<?php
/**
 * Formulario para bares con AJAX
 */

// Registrar el shortcode para el formulario de bares.
add_shortcode( 'form-bar', 'cdb_form_bar' );

/**
 * Mostrar el formulario para bares.
 */
function cdb_form_bar() {
    // Comprobar si el usuario está conectado.
    if ( ! is_user_logged_in() ) {
        return '<p>Debes iniciar sesión para actualizar el estado de tu bar.</p>';
    }

    // Obtener el ID del usuario actual.
    $user_id = get_current_user_id();

    // Obtener el bar asignado al usuario.
    $bar = get_posts(array(
        'post_type'  => 'bar',
        'author'     => $user_id,
        'posts_per_page' => 1
    ));

    if (empty($bar)) {
        return '<p>No tienes un bar registrado. Crea uno antes de actualizar su estado.</p>';
    }

    $bar_id = $bar[0]->ID;
    $estado_actual = get_post_meta( $bar_id, 'estado', true );

    // Generar el formulario con AJAX.
    ob_start();
    ?>
    <form id="cdb-update-estado-bar" method="post">
        <label for="estado">Estado del Bar:</label>
        <select name="estado" id="estado">
            <option value="Abierto todo el año" <?php selected($estado_actual, 'Abierto todo el año'); ?>>Abierto todo el año</option>
            <option value="Abierto temporalmente" <?php selected($estado_actual, 'Abierto temporalmente'); ?>>Abierto temporalmente</option>
            <option value="Cerrado temporalmente" <?php selected($estado_actual, 'Cerrado temporalmente'); ?>>Cerrado temporalmente</option>
            <option value="Cerrado permanente" <?php selected($estado_actual, 'Cerrado permanente'); ?>>Cerrado permanente</option>
            <option value="Traspaso" <?php selected($estado_actual, 'Traspaso'); ?>>Traspaso</option>
            <option value="Desconocido" <?php selected($estado_actual, 'Desconocido'); ?>>Desconocido</option>
        </select>
        <input type="hidden" name="bar_id" value="<?php echo esc_attr($bar_id); ?>">
        <input type="hidden" name="security" value="<?php echo wp_create_nonce('cdb_form_nonce'); ?>">
        <button type="submit">Actualizar</button>
    </form>
    <p id="cdb-response-message-bar" style="color: green;"></p>
    <?php
    return ob_get_clean();
}
?>
