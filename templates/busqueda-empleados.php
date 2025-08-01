<?php
// Nueva plantilla para [cdb_busqueda_empleados]
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<style>
.cdb-busqueda-filtros{margin-bottom:1em;display:flex;flex-wrap:wrap;gap:10px}
.cdb-busqueda-filtros input{padding:4px}
.cdb-busqueda-table{width:100%;border-collapse:collapse}
.cdb-busqueda-table th,.cdb-busqueda-table td{padding:6px;border-bottom:1px solid #ccc;text-align:left}
</style>
<div id="cdb-busqueda-empleados">
    <div class="cdb-busqueda-filtros">
        <input type="text" id="cdb-nombre" placeholder="<?php esc_attr_e('Nombre','cdb-form'); ?>" />
        <input type="text" id="cdb-posicion" placeholder="<?php esc_attr_e('Posici\xC3\xB3n','cdb-form'); ?>" />
        <input type="hidden" id="cdb-posicion-id" />
        <input type="text" id="cdb-bar" placeholder="<?php esc_attr_e('Bar','cdb-form'); ?>" />
        <input type="hidden" id="cdb-bar-id" />
        <input type="text" id="cdb-anio" placeholder="<?php esc_attr_e('A\xC3\xB1o','cdb-form'); ?>" />
    </div>
    <div id="cdb-busqueda-empleados-resultados">
        <?php include CDB_FORM_PATH . 'templates/busqueda-empleados-table.php'; ?>
    </div>
</div>
