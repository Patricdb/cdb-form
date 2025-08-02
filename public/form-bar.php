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
        return cdb_form_get_mensaje(
            'cdb_acceso_sin_login',
            __( 'Debes iniciar sesión para actualizar el estado de tu bar.', 'cdb-form' )
        );
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
        return cdb_form_render_mensaje(
            'cdb_mensaje_bar_sin_registro',
            'cdb_color_bar_sin_registro',
            __( 'No tienes un bar registrado. Crea uno antes de actualizar su estado.', 'cdb-form' )
        );
    }

    $bar_id = $bar[0]->ID;
    $estado_actual = get_post_meta( $bar_id, 'estado', true );

    // Generar el formulario con AJAX.
    ob_start();
    ?>
    <form id="cdb-update-estado-bar" method="post">
        <label for="estado"><?php esc_html_e( 'Estado del Bar:', 'cdb-form' ); ?></label>
        <select name="estado" id="estado">
            <option value="Abierto todo el año" <?php selected($estado_actual, 'Abierto todo el año'); ?>><?php esc_html_e( 'Abierto todo el año', 'cdb-form' ); ?></option>
            <option value="Abierto temporalmente" <?php selected($estado_actual, 'Abierto temporalmente'); ?>><?php esc_html_e( 'Abierto temporalmente', 'cdb-form' ); ?></option>
            <option value="Cerrado temporalmente" <?php selected($estado_actual, 'Cerrado temporalmente'); ?>><?php esc_html_e( 'Cerrado temporalmente', 'cdb-form' ); ?></option>
            <option value="Cerrado permanente" <?php selected($estado_actual, 'Cerrado permanente'); ?>><?php esc_html_e( 'Cerrado permanente', 'cdb-form' ); ?></option>
            <option value="Traspaso" <?php selected($estado_actual, 'Traspaso'); ?>><?php esc_html_e( 'Traspaso', 'cdb-form' ); ?></option>
            <option value="Desconocido" <?php selected($estado_actual, 'Desconocido'); ?>><?php esc_html_e( 'Desconocido', 'cdb-form' ); ?></option>
        </select>
        <input type="hidden" name="bar_id" value="<?php echo esc_attr($bar_id); ?>">
        <input type="hidden" name="security" value="<?php echo wp_create_nonce('cdb_form_nonce'); ?>">
        <button type="submit"><?php esc_html_e( 'Actualizar', 'cdb-form' ); ?></button>
    </form>
    <p id="cdb-response-message-bar" style="color: green;"></p>
    <?php
    return ob_get_clean();
}
?>
