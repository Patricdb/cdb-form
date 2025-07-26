<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$current_user = wp_get_current_user();
$existing_bar = get_posts(array(
    'post_type' => 'bar',
    'author'    => $current_user->ID,
    'posts_per_page' => 1
));

if (!empty($existing_bar)) {
    $bar_id = $existing_bar[0]->ID;
    $bar_nombre = get_the_title($bar_id);
    $bar_estado = get_post_meta($bar_id, 'estado', true);
} else {
    $bar_id = 0;
    $bar_nombre = '';
    $bar_estado = 'Abierto todo el año';
}
?>

<form id="cdb-form-bar" method="post">
    <?php wp_nonce_field( 'cdb_form_nonce', 'security' ); ?>
    <input type="hidden" name="bar_id" value="<?php echo esc_attr($bar_id); ?>">

    <label for="nombre_bar"><?php esc_html_e('Nombre del Bar:', 'cdb-form'); ?></label>
    <input type="text" id="nombre_bar" name="nombre_bar" value="<?php echo esc_attr($bar_nombre); ?>" required>

    <label for="estado"><?php esc_html_e('Estado:', 'cdb-form'); ?></label>
    <select id="estado" name="estado">
        <option value="Abierto todo el año" <?php selected($bar_estado, 'Abierto todo el año'); ?>>Abierto todo el año</option>
        <option value="Abierto temporalmente" <?php selected($bar_estado, 'Abierto temporalmente'); ?>>Abierto temporalmente</option>
        <option value="Cerrado temporalmente" <?php selected($bar_estado, 'Cerrado temporalmente'); ?>>Cerrado temporalmente</option>
        <option value="Cerrado permanente" <?php selected($bar_estado, 'Cerrado permanente'); ?>>Cerrado permanente</option>
        <option value="Traspaso" <?php selected($bar_estado, 'Traspaso'); ?>>Traspaso</option>
        <option value="Desconocido" <?php selected($bar_estado, 'Desconocido'); ?>>Desconocido</option>
    </select>

    <button type="submit"><?php esc_html_e('Actualizar', 'cdb-form'); ?></button>
</form>
