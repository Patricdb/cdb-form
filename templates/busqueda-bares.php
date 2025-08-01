<?php
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
#cdb-busqueda-bares-spinner{display:none;margin-top:10px}
#cdb-busqueda-bares .awesomplete > ul{background:#FAF8EE}
#cdb-busqueda-bares .awesomplete > ul > li[aria-selected="true"]{background:#E6E1D3}
</style>
<div id="cdb-busqueda-bares">
    <div class="cdb-busqueda-filtros">
        <input type="text" id="cdb-bar-nombre" placeholder="<?php esc_attr_e('Nombre','cdb-form'); ?>" />
        <input type="text" id="cdb-zona" placeholder="<?php esc_attr_e('Zona','cdb-form'); ?>" />
        <input type="hidden" id="cdb-zona-id" />
        <input type="text" id="cdb-apertura" placeholder="<?php esc_attr_e('Año','cdb-form'); ?>" />
        <button id="cdb-bar-filtrar" class="cdb-btn-filtrar" type="button"><?php esc_html_e('Filtrar','cdb-form'); ?></button>
        <button id="cdb-bar-limpiar" class="cdb-btn-filtrar" type="button"><?php esc_html_e('Limpiar','cdb-form'); ?></button>
    </div>
    <div id="cdb-busqueda-bares-resultados">
        <?php include CDB_FORM_PATH . 'templates/busqueda-bares-table.php'; ?>
    </div>
    <div id="cdb-busqueda-bares-spinner"><?php esc_html_e('Cargando...','cdb-form'); ?></div>
</div>
