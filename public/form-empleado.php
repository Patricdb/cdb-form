<?php
/**
 * Formulario para empleados con AJAX
 */

// Registrar el shortcode para el formulario de empleados.
add_shortcode( 'form-empleado', 'cdb_form_empleado' );

/**
 * Verifica si el usuario actual tiene el rol "Empleado".
 *
 * @return bool
 */
function cdb_usuario_es_empleado() {
    $current_user = wp_get_current_user();
    return in_array('empleado', (array) $current_user->roles);
}

/**
 * Mostrar el formulario para empleados.
 *
 * @return string HTML del formulario o mensaje de acceso restringido.
 */
function cdb_form_empleado() {
    // Comprobar si el usuario está conectado.
    if ( ! is_user_logged_in() ) {
        return '<p style="color: red;">' . esc_html__( 'Debes iniciar sesión para actualizar tu estado.', 'cdb-form' ) . '</p>';
    }

    // Comprobar si el usuario tiene el rol "Empleado".
    if ( ! cdb_usuario_es_empleado() ) {
        return '<p style="color: red;">' . esc_html__( 'No tienes permisos para acceder a esta sección.', 'cdb-form' ) . '</p>';
    }

    // Obtener el ID del usuario actual.
    $user_id = get_current_user_id();

    // Obtener el empleado asignado al usuario.
    $empleado = get_posts([
        'post_type'      => 'empleado',
        'author'         => $user_id,
        'posts_per_page' => 1
    ]);

    if (empty($empleado)) {
        return '<p style="color: red;">' . esc_html__( 'No tienes un perfil de empleado. Crea uno antes de actualizar tu disponibilidad.', 'cdb-form' ) . '</p>';
    }

    $empleado_id = $empleado[0]->ID;
    $disponible  = get_post_meta($empleado_id, 'disponible', true);

    // Generar el formulario con AJAX.
    ob_start();
    ?>
    <form id="cdb-update-disponibilidad" method="post">
        <label for="disponible"><strong><?php esc_html_e( '¿Estás disponible?', 'cdb-form' ); ?></strong></label>
        <select name="disponible" id="disponible">
            <option value="1" <?php selected($disponible, 1); ?>><?php esc_html_e( 'Sí', 'cdb-form' ); ?></option>
            <option value="0" <?php selected($disponible, 0); ?>><?php esc_html_e( 'No', 'cdb-form' ); ?></option>
        </select>
        <input type="hidden" name="empleado_id" value="<?php echo esc_attr($empleado_id); ?>">
        <input type="hidden" name="security" value="<?php echo wp_create_nonce('cdb_form_nonce'); ?>">
        <button type="submit"><?php esc_html_e( 'Actualizar', 'cdb-form' ); ?></button>
    </form>
    <p id="cdb-response-message" style="color: green;"></p>

    <script>
    jQuery(document).ready(function($) {
        $('#cdb-update-disponibilidad').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serialize();

            $.post('<?php echo admin_url('admin-ajax.php'); ?>', formData, function(response) {
                if (response.success) {
                    $('#cdb-response-message').text(response.message).css('color', 'green');
                } else {
                    $('#cdb-response-message').text(response.message).css('color', 'red');
                }
            }, 'json');
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
?>
