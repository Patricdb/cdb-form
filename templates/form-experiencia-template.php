<?php
// Asegurar que WordPress está cargado.
if (!defined('ABSPATH')) {
    exit;
}

// Verificar si el usuario está conectado.
$current_user = wp_get_current_user();
if (!$current_user->exists()) {
    echo cdb_form_render_mensaje(
        'cdb_mensaje_login_requerido',
        'cdb_color_login_requerido',
        __( 'Debes iniciar sesión para gestionar tu empleado.', 'cdb-form' )
    );
    return;
}

// Obtener la ID del empleado asociado al usuario actual mediante la función específica.
$empleado_id = (int) cdb_obtener_empleado_id($current_user->ID);
if (!$empleado_id) {
    echo cdb_form_get_mensaje(
        'cdb_experiencia_sin_perfil'
    );
    echo do_shortcode('[cdb_form_empleado]');
    return;
}

// (Opcional) Variables para inicializar campos en el formulario
$bar_id_actual   = '';
$anio_actual     = '';
$posicion_actual = '';

$fecha_apertura = '';
$fecha_cierre   = '';

error_log("[DEBUG] Bar ID: {$bar_id_actual} - Apertura: {$fecha_apertura} - Cierre: {$fecha_cierre}");
?>

<!-- Estilos visuales del formulario y la tabla de experiencia -->
<style>
    .cdb-experiencia-form {
        margin-top: 20px;
        padding: 15px;
        border: 1px solid #cdb888;
        border-radius: 8px;
        background-color: #FAF8EE;
    }
    .cdb-experiencia-form h2 {
        margin-bottom: 15px;
        font-size: 1.5em;
    }
    .cdb-experiencia-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .cdb-experiencia-form select,
    .cdb-experiencia-form input[type="number"],
    .cdb-experiencia-form input[type="text"] {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #cdb888;
        border-radius: 4px;
    }
    .cdb-experiencia-form button {
        padding: 10px 20px;
        background-color: black;
        color: #FAF8EE;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .cdb-experiencia-form button:hover {
        background-color: #444;
    }
    /* Estilos para el botón de borrado con Dashicons */
    .cdb-btn-borrar {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        margin: 0;
        color: #555;
    }
    .cdb-btn-borrar:hover {
        color: #222;
    }
    /* Estilo para el icono de “ojo” */
    .cdb-btn-ver {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        margin-right: 6px;
        color: #555;
        text-decoration: none; /* Se muestra como icono, sin subrayado */
    }
    .cdb-btn-ver:hover {
        color: #222;
    }
    /* Estilos para la tabla de experiencias */
    .cdb-experiencia-lista table {
        width: 100%;
        border-collapse: collapse;
    }
    .cdb-experiencia-lista table th,
    .cdb-experiencia-lista table td {
        text-align: left;
        padding: 8px;
    }
    .cdb-experiencia-lista table th {
        font-weight: bold;
        background-color: #cdb888;
    }
    .ui-menu .ui-state-active {
    background-color: black !important; /* Color de fondo */
    color: white !important; /* Color del texto */
    border: none !important; /* Elimina el borde azul */
    outline: none !important; /* Evita el resplandor azul en algunos navegadores */
    box-shadow: 0 0 0 2px white !important; /* Opcional: Cambia el borde a otro color */
    }

</style>

<!-- Sección de experiencias previas -->
<p><strong>Tu experiencia laboral:</strong></p>

<div class="cdb-experiencia-lista">
    <?php
    global $wpdb;

    // Consulta para obtener las experiencias del empleado uniendo con los posts de tipo "bar" y "cdb_posiciones".
    $experiencias = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT exp.id AS exp_id,
                    exp.anio,
                    exp.equipo_id,
                    p.ID AS bar_id,
                    p.post_title AS bar,
                    pos.ID AS posicion_id,
                    pos.post_title AS posicion
             FROM {$wpdb->prefix}cdb_experiencia exp
             JOIN {$wpdb->prefix}posts p ON exp.bar_id = p.ID
             JOIN {$wpdb->prefix}posts pos ON exp.posicion_id = pos.ID
             WHERE exp.empleado_id = %d
               AND p.post_type = 'bar'
               AND p.post_status = 'publish'
               AND pos.post_type = 'cdb_posiciones'
               AND pos.post_status = 'publish'
             ORDER BY exp.anio DESC",
            $empleado_id
        )
    );
    ?>

    <?php if (!empty($experiencias)) : ?>
        <table>
            <thead>
                <tr>
                    <th>Año</th>
                    <th>Bar</th>
                    <th>Posición</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($experiencias as $exp) : ?>
                    <tr>
                        <td><?php echo esc_html($exp->anio); ?></td>
                        <!-- Nombre del bar enlazado a su página -->
                        <td>
                            <a href="<?php echo esc_url(get_permalink($exp->bar_id)); ?>">
                                <?php echo esc_html($exp->bar); ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(get_permalink($exp->posicion_id)); ?>">
                                <?php echo esc_html($exp->posicion); ?>
                            </a>
                        </td>
                        <td>
                            <!-- Icono de “ojo” para ver el equipo, si existe -->
                            <?php if (!empty($exp->equipo_id)) : ?>
                                <a class="cdb-btn-ver" href="<?php echo esc_url(get_permalink($exp->equipo_id)); ?>" title="Ver equipo">
                                    <span class="dashicons dashicons-visibility"></span>
                                </a>
                            <?php endif; ?>
                            <!-- Botón de borrado de experiencia -->
                            <button class="cdb-btn-borrar" data-exp-id="<?php echo esc_attr($exp->exp_id); ?>" title="Eliminar experiencia">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <?php echo cdb_form_render_mensaje(
            'cdb_mensaje_empleado_sin_experiencia',
            'cdb_color_empleado_sin_experiencia',
            __( 'Aún no has registrado ninguna experiencia laboral.', 'cdb-form' )
        ); ?>
    <?php endif; ?>
</div>

<!-- Formulario para agregar/actualizar experiencia -->
<div class="cdb-experiencia-form">
    <h2><?php esc_html_e( 'Actualizar Experiencia Laboral', 'cdb-form' ); ?></h2>
    <form id="cdb_experiencia_form">
        <!-- Acción para el AJAX -->
        <input type="hidden" name="action" value="cdb_guardar_experiencia">
        <!-- Nonce de seguridad -->
        <input type="hidden" name="security" value="<?php echo wp_create_nonce('cdb_form_nonce'); ?>">
        <!-- Se envía el empleado_id, aunque en el handler se obtiene de forma consistente -->
        <input type="hidden" name="empleado_id" value="<?php echo esc_attr($empleado_id); ?>">
        <!-- Campo oculto para vincular equipo si es necesario -->
        <input type="hidden" name="relacionar_equipo" value="1">

        <?php
        // 1) Obtener todos los bares y crear array para Autocomplete
        $bares = get_posts(array(
            'post_type'   => 'bar',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
            'post_status' => 'publish'
        ));

        $lista_bares = [];
        foreach ($bares as $bar) {
            $lista_bares[] = [
                'label' => $bar->post_title,
                'value' => $bar->post_title, // lo que se muestra en el input
                'id'    => $bar->ID,         // ID real para guardar
            ];
        }
        ?>

        <!-- Campo de autocompletado para Bar -->
        <label for="bar-search">Bar:</label>
        <input 
            type="text" 
            id="bar-search" 
            name="bar_search" 
            placeholder="Escribe el nombre del bar" 
            required
        >
        <!-- Hidden donde guardamos el ID real del bar seleccionado -->
        <input type="hidden" id="bar_id" name="bar_id" value="">

        <!-- Selección de Año (se actualizará dinámicamente) -->
        <label for="anio">Año:</label>
        <select name="anio" id="anio" required>
            <option value="">Selecciona un bar primero</option>
        </select>

        <!-- Selección de Posición (CPT "cdb_posiciones") -->
        <label for="posicion_id">Posición:</label>
        <select name="posicion_id" id="posicion_id" required>
            <option value="">Selecciona una posición</option>
            <?php
            // Obtener todas las posiciones (CPT "cdb_posiciones")
            $posiciones = get_posts(array(
                'post_type'      => 'cdb_posiciones',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'post_status'    => 'publish'
            ));
            foreach ($posiciones as $posicion) {
                echo '<option value="' . esc_attr($posicion->ID) . '">' . esc_html($posicion->post_title) . '</option>';
            }
            ?>
        </select>

        <button type="submit">Guardar Experiencia</button>
    </form>
</div>

<div id="cdb_experiencia_mensaje"></div>

<!-- Script para manejo de AJAX, carga dinámica y recarga de página -->
<script>
document.addEventListener("DOMContentLoaded", function() {

    // 2) Inicializar Autocomplete con la lista de bares
    var baresData = <?php echo wp_json_encode($lista_bares); ?>;

    jQuery(document).ready(function($) {
        $("#bar-search").autocomplete({
            source: baresData,
            select: function(event, ui) {
                // Al seleccionar un bar, guardamos su ID en el hidden
                $("#bar_id").val(ui.item.id);

                // Cargar dinámicamente los años (lo que antes estaba en change del <select>)
                const barID = ui.item.id;
                const anioSelect = document.getElementById("anio");

                if (barID) {
                    fetch("<?php echo admin_url('admin-ajax.php'); ?>?action=cdb_obtener_anios_bar&bar_id=" + barID)
                    .then(response => response.json())
                    .then(data => {
                        anioSelect.innerHTML = '<option value="">Selecciona un año</option>';
                        if (data.success && data.data.fecha_apertura) {
                            let apertura = parseInt(data.data.fecha_apertura);
                            let cierre = data.data.fecha_cierre ? parseInt(data.data.fecha_cierre) : new Date().getFullYear();

                            if (apertura && cierre && apertura <= cierre) {
                                for (let i = cierre; i >= apertura; i--) {
                                    let option = document.createElement("option");
                                    option.value = i;
                                    option.textContent = i;
                                    anioSelect.appendChild(option);
                                }
                            }
                        } else {
                            console.error("Error obteniendo años:", data.message);
                            anioSelect.innerHTML = "<option value=''>No se encontraron fechas configuradas.</option>";
                        }
                    })
                    .catch(error => {
                        console.error("Error cargando los años:", error);
                        anioSelect.innerHTML = "<option value=''>Error cargando años.</option>";
                    });
                } else {
                    anioSelect.innerHTML = '<option value="">Selecciona un bar primero</option>';
                }
            }
        });
    });

    // Manejo del envío del formulario de experiencia.
    document.getElementById("cdb_experiencia_form").addEventListener("submit", function(event) {
        event.preventDefault();
        let formData = new FormData(this);

        fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar mensaje de éxito
                document.getElementById("cdb_experiencia_mensaje").innerHTML = "<p>" + data.data.message + "</p>";
                // Si se indica recarga, refrescar la página completa.
                if (data.data.reload) {
                    window.location.reload();
                }
                else {
                    // Alternativamente, se podría actualizar dinámicamente la lista de experiencias.
                    fetch("<?php echo admin_url('admin-ajax.php'); ?>?action=cdb_listar_experiencias&empleado_id=<?php echo $empleado_id; ?>")
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector(".cdb-experiencia-lista").innerHTML = html;
                    });
                }
            } else {
                document.getElementById("cdb_experiencia_mensaje").innerHTML = "<p>Error: " + data.message + "</p>";
            }
        })
        .catch(error => console.error("Error:", error));
    });

});

// Listener para el botón de borrado de experiencia.
document.addEventListener('click', function(e) {
    const button = e.target.closest('.cdb-btn-borrar');
    if (button) {
        const expId = button.getAttribute('data-exp-id');
        if (confirm('¿Estás seguro de que deseas eliminar esta experiencia?')) {
            fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
                method: "POST",
                body: new URLSearchParams({
                    action: 'cdb_borrar_experiencia',
                    security: '<?php echo wp_create_nonce('cdb_form_nonce'); ?>',
                    exp_id: expId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.data.message || 'Experiencia eliminada correctamente.');
                    // Recargar la página completa tras el borrado.
                    window.location.reload();
                } else {
                    alert("Error: " + (data.message || 'No se pudo eliminar la experiencia.'));
                }
            })
            .catch(error => console.error("Error:", error));
        }
    }
});
</script>
