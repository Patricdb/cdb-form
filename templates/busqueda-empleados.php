<?php
// Nueva plantilla para [cdb_busqueda_empleados]
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<style>
.cdb-busqueda-filtros{margin-bottom:1em;display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end}
.cdb-busqueda-filtros input{padding:4px}
.cdb-btn-filtrar{padding:6px 12px;background:#000;color:#FAF8EE;border:0;border-radius:4px;cursor:pointer}
.cdb-btn-filtrar:hover{background:#444}
.cdb-busqueda-table{width:100%;border-collapse:collapse}
.cdb-busqueda-table th,.cdb-busqueda-table td{padding:6px;border-bottom:1px solid #ccc;text-align:left}
</style>
<div id="cdb-busqueda-empleados">
    <div class="cdb-busqueda-filtros">
        <input type="text" id="cdb-nombre" placeholder="<?php esc_attr_e('Nombre','cdb-form'); ?>" />
        <input type="text" id="cdb-posicion" placeholder="<?php esc_attr_e('Posición','cdb-form'); ?>" />
        <input type="hidden" id="cdb-posicion-id" />
        <input type="text" id="cdb-bar" placeholder="<?php esc_attr_e('Bar','cdb-form'); ?>" />
        <input type="hidden" id="cdb-bar-id" />
        <input type="text" id="cdb-anio" placeholder="<?php esc_attr_e('Año','cdb-form'); ?>" />
        <button id="cdb-filtrar" class="cdb-btn-filtrar" type="button"><?php esc_html_e('Filtrar','cdb-form'); ?></button>
    </div>
    <div id="cdb-busqueda-empleados-resultados">
        <?php include CDB_FORM_PATH . 'templates/busqueda-empleados-table.php'; ?>
    </div>
</div>
