<?php
/**
 * Plugin Name: CdB Form
 * Description: Plugin para gestionar formularios de los tipos de contenido empleado y bar.
 * Version: 1.8.1
 * Author: CdB_
 * Author URI: https://proyectocdb.es
 * Text Domain: cdb-form
 * Domain Path: /languages
 */

// Evitar acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*---------------------------------------------------------------
 * 1. DEFINICIÓN DE CONSTANTES
 *---------------------------------------------------------------*/
define( 'CDB_FORM_PATH', plugin_dir_path( __FILE__ ) );
define( 'CDB_FORM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Carga las traducciones del plugin.
 */
add_action(
    'init',
    function () {
        load_plugin_textdomain( 'cdb-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
);

/*---------------------------------------------------------------
 * 2. CARGA OPCIONAL DEL PLUGIN cdb-bar (SI EXISTE)
 *---------------------------------------------------------------
 *   - Verifica si la función cdb_get_or_create_equipo está definida.
 *   - Si no lo está, busca el archivo cpt-equipo.php del plugin cdb-bar
 *     para cargar la lógica de creación de equipos.
 *---------------------------------------------------------------*/
if ( ! function_exists('cdb_get_or_create_equipo') ) {
    $cdb_bar_includes = WP_PLUGIN_DIR . '/cdb-bar/includes/cpt-equipo.php';
    if ( file_exists( $cdb_bar_includes ) ) {
        require_once $cdb_bar_includes;
    }
}

/*---------------------------------------------------------------
 * 3. INCLUSIÓN DE ARCHIVOS NECESARIOS
 *---------------------------------------------------------------
 *   - init.php: Inicializa componentes básicos del plugin.
 *   - post-types.php: Registra CPTs necesarios (empleado, posiciones, etc.).
 *   - database.php: Contiene la lógica para crear tablas personalizadas.
 *   - ajax-functions.php: Maneja las acciones AJAX (guardar/borrar experiencias, etc.).
 *---------------------------------------------------------------*/
require_once CDB_FORM_PATH . 'includes/init.php';
require_once CDB_FORM_PATH . 'includes/post-types.php';
require_once CDB_FORM_PATH . 'includes/database.php';
require_once CDB_FORM_PATH . 'includes/ajax-functions.php';

/*---------------------------------------------------------------
 * 4. HOOKS DE ACTIVACIÓN/DESACTIVACIÓN DEL PLUGIN
 *---------------------------------------------------------------*/

/**
 * Función que se ejecuta al activar el plugin.
 * - Crea la tabla cdb_experiencia (y cdb_posiciones si fuera necesario).
 */
if (!function_exists('cdb_form_activate')) {
    function cdb_form_activate() {
        // Llamar a la función que crea la tabla cdb_experiencia.
        // (y, si no se ha comentado, la tabla cdb_posiciones).
        // Ahora las posiciones se gestionan como CPT, por lo que
        // la tabla cdb_posiciones podría no ser necesaria.
        cdb_form_create_tables();
    }
}
register_activation_hook(__FILE__, 'cdb_form_activate');

/**
 * Función que se ejecuta al desactivar el plugin.
 * - Aquí podríamos limpiar opciones o revertir cambios si fuera necesario.
 */
function cdb_form_deactivate() {
    // En este ejemplo no hacemos nada específico al desactivar.
}
register_deactivation_hook( __FILE__, 'cdb_form_deactivate' );

/*---------------------------------------------------------------
 * 5. ACCIÓN AJAX PARA REFRESCAR "TOP 21"
 *---------------------------------------------------------------
 *   - Devuelve el HTML resultante del shortcode
 *     [cdb_top_empleados_experiencia_precalculada].
 *   - Permite que, tras guardar o borrar experiencias,
 *     se pueda refrescar esta clasificación sin recargar la página.
 *---------------------------------------------------------------*/

/**
 * Acción AJAX para refrescar dinámicamente la tabla "Top 21" en el frontend,
 * devolviendo el HTML del shortcode [cdb_top_empleados_experiencia_precalculada].
 */
function cdb_refrescar_top_21() {
    // Retorna el HTML del shortcode para la tabla de los 21 empleados con mayor puntuación.
    echo do_shortcode('[cdb_top_empleados_experiencia_precalculada]');
    wp_die();
}
add_action('wp_ajax_cdb_refrescar_top_21', 'cdb_refrescar_top_21');
add_action('wp_ajax_nopriv_cdb_refrescar_top_21', 'cdb_refrescar_top_21');

function cdb_form_enqueue_scripts_conditionally() {
    // Verificar si se usa [cdb_experiencia] o [cdb_busqueda_empleados]
    global $post;
    if ( ! is_a( $post, 'WP_Post' ) ) {
        return;
    }
    $needs_main = false;

    if ( has_shortcode( $post->post_content, 'cdb_experiencia' ) ) {
        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_style(
            'cdb-form-jquery-ui-css',
            'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
            array(),
            '1.12.1'
        );
        $needs_main = true;
    }

    if ( has_shortcode( $post->post_content, 'cdb_busqueda_empleados' ) || has_shortcode( $post->post_content, 'cdb_busqueda_bares' ) ) {
        wp_enqueue_script(
            'awesomplete',
            'https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.5/awesomplete.min.js',
            array(),
            '1.1.5',
            true
        );
        wp_enqueue_style(
            'awesomplete',
            'https://cdnjs.cloudflare.com/ajax/libs/awesomplete/1.1.5/awesomplete.min.css',
            array(),
            '1.1.5'
        );
        wp_add_inline_script('awesomplete', 'document.dispatchEvent(new Event("awesomplete-loaded"));');

        // Encolar el script principal después de Awesomplete
        wp_enqueue_script( 'cdb-form-frontend-script' );
        $needs_main = false;
    }

    if ( $needs_main ) {
        wp_enqueue_script( 'cdb-form-frontend-script' );
    }
}
add_action( 'wp_enqueue_scripts', 'cdb_form_enqueue_scripts_conditionally' );



