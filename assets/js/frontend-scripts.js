jQuery(document).ready(function($) {
    // 🔹 Manejo de actualización de disponibilidad del empleado
    $('#cdb-update-disponibilidad').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        console.log("Datos enviados (Empleado):", formData); // Depuración en consola

        $.ajax({
            type: 'POST',
            url: cdb_form_ajax.ajaxurl,
            data: formData + '&action=cdb_actualizar_disponibilidad',
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta AJAX (Empleado):", response); // Depuración en consola

                if (response.success) {
                    alert('Disponibilidad actualizada correctamente.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX (Empleado):", textStatus, errorThrown);
                alert('Hubo un problema al actualizar la disponibilidad.');
            }
        });
    });

    // 🔹 Manejo de actualización del estado del bar
    $('#cdb-update-estado-bar').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();

        console.log("Datos enviados (Bar):", formData); // Depuración en consola

        $.ajax({
            type: 'POST',
            url: cdb_form_ajax.ajaxurl,
            data: formData + '&action=cdb_actualizar_estado_bar',
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta AJAX (Bar):", response); // Depuración en consola

                if (response.success) {
                    alert('Estado del bar actualizado correctamente.');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error AJAX (Bar):", textStatus, errorThrown);
                alert('Hubo un problema al actualizar el estado del bar.');
            }
        });
    });
    // Buscador avanzado de empleados con AJAX
    function cdbBuscarEmpleados() {
        var data = {
            action: 'cdb_buscar_empleados',
            nonce: cdb_form_ajax.nonce,
            nombre: jQuery('#cdb-nombre').val(),
            posicion_id: jQuery('#cdb-posicion-id').val(),
            bar_id: jQuery('#cdb-bar-id').val(),
            anio: jQuery('#cdb-anio').val()
        };
        jQuery.getJSON(cdb_form_ajax.ajaxurl, data, function(resp){
            if(resp.success){
                jQuery('#cdb-busqueda-empleados-resultados').html(resp.data.html);
            }
        });
    }


    // Ejecuta la búsqueda solo cuando el usuario pulsa el botón "Filtrar"
    jQuery('#cdb-filtrar').on('click', function(){
        cdbBuscarEmpleados();
    });

    // Autocompletados
    // Si se añaden más filtros, replicar esta llamada cambiando el parámetro
    // "tipo" para que el backend devuelva las sugerencias correspondientes.
    jQuery('#cdb-nombre').autocomplete({
        source: function(request, response){
            jQuery.getJSON(cdb_form_ajax.ajaxurl, {action:'cdb_sugerencias', nonce:cdb_form_ajax.nonce, tipo:'nombre', term:request.term}, response);
        },
        minLength:2
    });

    jQuery('#cdb-posicion').autocomplete({
        source: function(request, response){
            jQuery.getJSON(cdb_form_ajax.ajaxurl, {action:'cdb_sugerencias', nonce:cdb_form_ajax.nonce, tipo:'posicion', term:request.term}, response);
        },
        minLength:2,
        select: function(e,ui){ jQuery('#cdb-posicion-id').val(ui.item.id); }
    }).on('keyup', function(){ jQuery('#cdb-posicion-id').val(''); });

    jQuery('#cdb-bar').autocomplete({
        source: function(request, response){
            jQuery.getJSON(cdb_form_ajax.ajaxurl, {action:'cdb_sugerencias', nonce:cdb_form_ajax.nonce, tipo:'bar', term:request.term}, response);
        },
        minLength:2,
        select: function(e,ui){ jQuery('#cdb-bar-id').val(ui.item.id); }
    }).on('keyup', function(){ jQuery('#cdb-bar-id').val(''); });

    jQuery('#cdb-anio').autocomplete({
        source: function(request, response){
            jQuery.getJSON(cdb_form_ajax.ajaxurl, {action:'cdb_sugerencias', nonce:cdb_form_ajax.nonce, tipo:'anio', term:request.term}, response);
        },
        minLength:1
    });

    // Carga inicial
    cdbBuscarEmpleados();
});

