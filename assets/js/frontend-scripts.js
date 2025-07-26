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

    // 🔹 Manejo del formulario de bar (crear/actualizar)
    $('#cdb-form-bar').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'cdb_actualizar_estado_bar',
            security: $('#security').val(),
            bar_id: $('input[name="bar_id"]').val(),
            estado: $('#estado').val()
        };

        $.post(cdb_form_ajax.ajaxurl, formData, function(response) {
            alert(response.message);
            if (response.success) {
                location.reload();
            }
        }, 'json');
    });

    // 🔹 Manejo del formulario de empleado
    $('#cdb-form-empleado').on('submit', function(e) {
        e.preventDefault();

        var formData = {
            action: 'cdb_form_empleado_submit',
            security: $('#security').val(),
            empleado_id: $('input[name="empleado_id"]').val(),
            nombre: $('#nombre').val(),
            disponible: $('#disponible').val()
        };

        $.post(cdb_form_ajax.ajaxurl, formData, function(response) {
            if (response.success) {
                alert(response.message || 'Perfil de empleado actualizado con éxito.');
                location.reload();
            } else {
                alert(response.message || 'Hubo un error inesperado.');
            }
        }, 'json').fail(function(jqXHR, textStatus) {
            alert('Error en la solicitud: ' + textStatus);
        });
    });
});
