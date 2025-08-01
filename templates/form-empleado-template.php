<?php
// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Verificar si el usuario está conectado
$current_user = wp_get_current_user();
if (!$current_user->exists()) {
    echo '<p>' . esc_html__( 'Debes iniciar sesión para gestionar tu empleado.', 'cdb-form' ) . '</p>';
    return;
}

// Obtener el empleado si ya existe
$existing_empleado = get_posts(array(
    'post_type'      => 'empleado',
    'author'         => $current_user->ID,
    'posts_per_page' => 1,
));

// Si existe empleado, tomamos su ID y datos; de lo contrario, ponemos valores por defecto
if (!empty($existing_empleado)) {
    $empleado_id         = $existing_empleado[0]->ID;
    $empleado_nombre     = get_the_title($empleado_id);
    $empleado_disponible = get_post_meta($empleado_id, 'disponible', true) ?: '1';
    $button_text         = __( 'Actualizar Empleado', 'cdb-form' );
} else {
    $empleado_id         = 0;
    $empleado_nombre     = '';
    $empleado_disponible = '1';
    $button_text         = __( 'Crear Empleado', 'cdb-form' );
}
?>

<div class="cdb-empleado-container">
    <!-- Formulario para crear/actualizar empleado -->
    <form id="cdb-form-empleado" method="post">
        <?php wp_nonce_field('cdb_form_nonce', 'security'); ?>

        <input type="hidden" name="empleado_id" value="<?php echo esc_attr($empleado_id); ?>">

        <label for="nombre"><?php esc_html_e( 'Nombre:', 'cdb-form' ); ?></label>
        <input type="text" id="nombre" name="nombre" value="<?php echo esc_attr($empleado_nombre); ?>" required>

        <label for="disponible"><?php esc_html_e( 'Disponible:', 'cdb-form' ); ?></label>
        <select id="disponible" name="disponible">
            <option value="1" <?php selected($empleado_disponible, '1'); ?>><?php esc_html_e( 'Sí', 'cdb-form' ); ?></option>
            <option value="0" <?php selected($empleado_disponible, '0'); ?>><?php esc_html_e( 'No', 'cdb-form' ); ?></option>
        </select>

        <button type="submit"><?php echo esc_html($button_text); ?></button>
    </form>
</div>

<style>
    .cdb-empleado-container {
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
    }
    
    .cdb-empleado-container h2 {
        font-size: 1.8em;
        margin-bottom: 15px;
    }

    #cdb-form-empleado {
        margin-top: 20px;
        padding: 15px;
        background: #fafafa;
        border: 1px solid #ddd;
        border-radius: 8px;
    }

    #cdb-form-empleado label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
    }

    #cdb-form-empleado input,
    #cdb-form-empleado select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    #cdb-form-empleado button {
        display: block;
        width: 100%;
        padding: 10px;
        margin-top: 15px;
        background: black;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    #cdb-form-empleado button:hover {
        background: #333;
    }
</style>

<script>
jQuery(document).ready(function($) {
    $('#cdb-form-empleado').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'cdb_form_empleado_submit',
            security: $('#security').val(),
            empleado_id: $('input[name="empleado_id"]').val(),
            nombre: $('#nombre').val(),
            disponible: $('#disponible').val()
        };

        $.post('<?php echo admin_url('admin-ajax.php'); ?>', formData, function(response) {
            if (response.success) {
                alert(response.message || 'Perfil de empleado actualizado con éxito.');
                location.reload();
            } else {
                alert(response.message || 'Hubo un error inesperado.');
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            alert('Error en la solicitud: ' + textStatus);
        });
    });
});
</script>
