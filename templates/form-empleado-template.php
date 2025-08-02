<?php
// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Verificar si el usuario está conectado
$current_user = wp_get_current_user();
if (!$current_user->exists()) {
    echo cdb_form_render_mensaje(
        'cdb_mensaje_login_requerido',
        'cdb_color_login_requerido',
        __( 'Debes iniciar sesión para gestionar tu empleado.', 'cdb-form' )
    );
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
    echo cdb_form_get_mensaje(
        'cdb_empleado_no_encontrado'
    );
    $empleado_id         = 0;
    $empleado_nombre     = '';
    $empleado_disponible = '1';
    $button_text         = __( 'Crear Empleado', 'cdb-form' );
}

// Opciones de diseño
$disenio = get_option('cdb_form_disenio_empleado');
$cdb_background            = isset($disenio['background_color']) ? $disenio['background_color'] : '#fafafa';
$cdb_border_color          = isset($disenio['border_color']) ? $disenio['border_color'] : '#ddd';
$cdb_text_color            = isset($disenio['text_color']) ? $disenio['text_color'] : '#000';
$cdb_button_bg             = isset($disenio['button_bg']) ? $disenio['button_bg'] : 'black';
$cdb_button_text           = isset($disenio['button_text_color']) ? $disenio['button_text_color'] : 'white';
$cdb_font_size             = isset($disenio['font_size']) ? intval($disenio['font_size']) : 14;
$cdb_padding               = isset($disenio['padding']) ? intval($disenio['padding']) : 20;
$cdb_field_spacing         = isset($disenio['field_spacing']) ? intval($disenio['field_spacing']) : 10;
$cdb_success_color         = isset($disenio['success_message_color']) ? $disenio['success_message_color'] : '#008000';
$cdb_error_color           = isset($disenio['error_message_color']) ? $disenio['error_message_color'] : '#FF0000';
$cdb_container_background  = isset($disenio['container_background_color']) ? $disenio['container_background_color'] : '#ffffff';
$cdb_margin_top            = isset($disenio['margin_top']) ? intval($disenio['margin_top']) : 0;
$cdb_margin_right          = isset($disenio['margin_right']) ? intval($disenio['margin_right']) : 0;
$cdb_margin_bottom         = isset($disenio['margin_bottom']) ? intval($disenio['margin_bottom']) : 0;
$cdb_margin_left           = isset($disenio['margin_left']) ? intval($disenio['margin_left']) : 0;
$cdb_alignment             = isset($disenio['alignment']) ? $disenio['alignment'] : 'center';
?>

<?php
    $alignment_css = '';
    if ( $cdb_alignment === 'center' ) {
        $alignment_css = 'margin-left:auto;margin-right:auto;';
    } elseif ( $cdb_alignment === 'right' ) {
        $alignment_css = 'margin-left:auto;margin-right:0;';
    } else {
        $alignment_css = 'margin-right:auto;margin-left:0;';
    }
?>

<div class="cdb-empleado-container" style="<?php
        echo 'margin-top:' . esc_attr( $cdb_margin_top ) . 'px;';
        echo 'margin-right:' . esc_attr( $cdb_margin_right ) . 'px;';
        echo 'margin-bottom:' . esc_attr( $cdb_margin_bottom ) . 'px;';
        echo 'margin-left:' . esc_attr( $cdb_margin_left ) . 'px;';
        echo $alignment_css;
    ?>">
    <!-- Formulario para crear/actualizar empleado -->
    <form id="cdb-form-empleado" method="post">
        <?php wp_nonce_field('cdb_form_nonce', 'security'); ?>

        <input type="hidden" name="empleado_id" value="<?php echo esc_attr($empleado_id); ?>" aria-hidden="true">

        <label for="nombre"><?php esc_html_e( 'Nombre:', 'cdb-form' ); ?></label>
        <input type="text" id="nombre" name="nombre" value="<?php echo esc_attr($empleado_nombre); ?>" required>

        <label for="disponible"><?php esc_html_e( 'Disponible:', 'cdb-form' ); ?></label>
        <select id="disponible" name="disponible">
            <option value="1" <?php selected($empleado_disponible, '1'); ?>><?php esc_html_e( 'Sí', 'cdb-form' ); ?></option>
            <option value="0" <?php selected($empleado_disponible, '0'); ?>><?php esc_html_e( 'No', 'cdb-form' ); ?></option>
        </select>

        <button type="submit"><?php echo esc_html($button_text); ?></button>
    </form>
    <p id="cdb-form-message" class="cdb-form-message" aria-live="polite"></p>
</div>

<style>
    .cdb-empleado-container {
        padding: <?php echo esc_attr($cdb_padding); ?>px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: <?php echo esc_attr($cdb_container_background); ?>;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        max-width: 600px;
    }
    
    .cdb-empleado-container h2 {
        font-size: 1.8em;
        margin-bottom: 15px;
    }

    #cdb-form-empleado {
        margin-top: 20px;
        padding: 15px;
        background: <?php echo esc_attr($cdb_background); ?>;
        border: 1px solid <?php echo esc_attr($cdb_border_color); ?>;
        border-radius: 8px;
    }

    #cdb-form-empleado label {
        font-weight: bold;
        display: block;
        margin-top: <?php echo esc_attr($cdb_field_spacing); ?>px;
        color: <?php echo esc_attr($cdb_text_color); ?>;
        font-size: <?php echo esc_attr($cdb_font_size); ?>px;
    }

    #cdb-form-empleado input,
    #cdb-form-empleado select {
        width: 100%;
        padding: 8px;
        margin-bottom: <?php echo esc_attr($cdb_field_spacing); ?>px;
        border: 1px solid #ccc;
        border-radius: 4px;
        color: <?php echo esc_attr($cdb_text_color); ?>;
        font-size: <?php echo esc_attr($cdb_font_size); ?>px;
    }

    #cdb-form-empleado button {
        display: block;
        width: 100%;
        padding: 10px;
        margin-top: 15px;
        background: <?php echo esc_attr($cdb_button_bg); ?>;
        color: <?php echo esc_attr($cdb_button_text); ?>;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    #cdb-form-empleado button:hover {
        background: #333;
    }

    .cdb-form-message.success {
        color: <?php echo esc_attr( $cdb_success_color ); ?>;
    }

    .cdb-form-message.error {
        color: <?php echo esc_attr( $cdb_error_color ); ?>;
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
            var $msg = $('#cdb-form-message');
            $msg.removeClass('success error');
            if (response.success) {
                $msg.addClass('success').text(response.message || 'Perfil de empleado actualizado con éxito.');
                location.reload();
            } else {
                $msg.addClass('error').text(response.message || 'Hubo un error inesperado.');
            }
        }, 'json')
        .fail(function(jqXHR, textStatus) {
            $('#cdb-form-message').addClass('error').text('Error en la solicitud: ' + textStatus);
        });
    });
});
</script>

