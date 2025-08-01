<?php
// Plantilla para el shortcode [cdb_busqueda_empleados]
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<style>
.cdb-busqueda-filtros{margin-bottom:1em;display:flex;flex-wrap:wrap;gap:10px}
.cdb-busqueda-filtros select,.cdb-busqueda-filtros input{padding:4px}
.cdb-busqueda-table{width:100%;border-collapse:collapse}
.cdb-busqueda-table th,.cdb-busqueda-table td{padding:6px;border-bottom:1px solid #ccc;text-align:left}
</style>
<form id="cdb-busqueda-empleados-form" class="cdb-busqueda-filtros" method="get">
    <select name="bar_id">
        <option value="0"><?php esc_html_e('Todos los Bares','cdb-form'); ?></option>
        <?php foreach($opciones_bar as $id=>$nombre): ?>
            <option value="<?php echo esc_attr($id); ?>" <?php selected($bar_id,$id); ?>><?php echo esc_html($nombre); ?></option>
        <?php endforeach; ?>
    </select>
    <select name="equipo_id">
        <option value="0"><?php esc_html_e('Todos los Equipos','cdb-form'); ?></option>
        <?php foreach($opciones_equipo as $id=>$nombre): ?>
            <option value="<?php echo esc_attr($id); ?>" <?php selected($equipo_id,$id); ?>><?php echo esc_html($nombre); ?></option>
        <?php endforeach; ?>
    </select>
    <select name="posicion_id">
        <option value="0"><?php esc_html_e('Todas las Posiciones','cdb-form'); ?></option>
        <?php foreach($opciones_posicion as $id=>$nombre): ?>
            <option value="<?php echo esc_attr($id); ?>" <?php selected($posicion_id,$id); ?>><?php echo esc_html($nombre); ?></option>
        <?php endforeach; ?>
    </select>
    <select name="anio">
        <option value="0"><?php esc_html_e('Todos los Años','cdb-form'); ?></option>
        <?php foreach($opciones_anio as $valor): ?>
            <option value="<?php echo esc_attr($valor); ?>" <?php selected($anio,$valor); ?>><?php echo esc_html($valor); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="text" name="nombre" placeholder="<?php esc_attr_e('Nombre','cdb-form'); ?>" value="<?php echo esc_attr($nombre); ?>" />
    <select name="disponible">
        <option value=""><?php esc_html_e('Disponibilidad','cdb-form'); ?></option>
        <option value="1" <?php selected($disponible,'1'); ?>><?php esc_html_e('Disponible','cdb-form'); ?></option>
        <option value="0" <?php if($disponible==='0') echo 'selected'; ?>><?php esc_html_e('No disponible','cdb-form'); ?></option>
    </select>
</form>
<?php if(empty($empleados)): ?>
    <p><?php esc_html_e('No se encontraron empleados con esos filtros.','cdb-form'); ?></p>
<?php else: ?>
<table class="cdb-busqueda-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Año','cdb-form'); ?></th>
            <th><?php esc_html_e('Empleado','cdb-form'); ?></th>
            <th><?php esc_html_e('Posición','cdb-form'); ?></th>
            <th><?php esc_html_e('Bar','cdb-form'); ?></th>
            <th><?php esc_html_e('Equipo','cdb-form'); ?></th>
            <th><?php esc_html_e('Puntuación','cdb-form'); ?></th>
            <th><?php esc_html_e('Disponibilidad','cdb-form'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($empleados as $emp): ?>
        <tr>
            <td><?php echo esc_html($emp['anio']); ?></td>
            <td><a href="<?php echo esc_url($emp['url']); ?>"><?php echo esc_html($emp['nombre']); ?></a></td>
            <td><?php echo esc_html($emp['posicion']); ?></td>
            <td><?php echo esc_html($emp['bar']); ?></td>
            <td><?php echo esc_html($emp['equipo']); ?></td>
            <td><?php echo esc_html($emp['puntuacion']); ?></td>
            <td><?php echo esc_html($emp['disponible']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
