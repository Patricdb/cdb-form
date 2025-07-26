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
});
