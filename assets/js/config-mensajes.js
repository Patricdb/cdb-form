jQuery(document).ready(function($){
    // Toggle edición de mensaje
    $('.cdb-edit-mensaje').on('click', function(){
        var cont = $(this).closest('.cdb-config-mensaje');
        cont.toggleClass('editing');
        cont.find('.cdb-mensaje-edicion').toggle();
    });

    // Actualizar preview de texto
    $('.cdb-mensaje-edicion textarea').on('input', function(){
        var cont   = $(this).closest('.cdb-config-mensaje');
        var prev   = cont.find('.cdb-mensaje-preview');
        var dest   = cont.find('textarea[data-role="destacado"]').val();
        var sec    = cont.find('textarea[data-role="secundario"]').val();
        prev.find('.cdb-mensaje-destacado').text(dest);
        prev.find('.cdb-mensaje-secundario').text(sec).toggle(sec.trim().length > 0);
    });

    // Actualizar preview de colores y clase
    $('.cdb-mensaje-edicion select').on('change', function(){
        var opt   = $(this).find('option:selected');
        var color = opt.data('color');
        var texto = opt.data('text');
        var cls   = opt.data('class');
        var cont  = $(this).closest('.cdb-config-mensaje');
        var prev  = cont.find('.cdb-mensaje-preview');
        prev.removeClass().addClass('cdb-mensaje-preview').addClass(cls);
        prev.css({
            'border-left-color': color,
            'background-color': color,
            'color': texto
        });
        cont.find('.cdb-clase-css').text(cls);
    }).trigger('change');

    // Añadir nuevo tipo/color
    $('#cdb-add-tipo-color').on('click', function(e){
        e.preventDefault();
        var idx = $('#cdb-tipos-color .cdb-tipo-color-row').length + 1;
        var row = $('<div class="cdb-tipo-color-row"></div>');
        row.append('<input type="text" name="tipos_color[new_'+idx+'][name]" placeholder="'+cdbMensajes.nuevoNombre+'" />');
        row.append('<input type="text" name="tipos_color[new_'+idx+'][class]" placeholder="'+cdbMensajes.nuevaClase+'" />');
        row.append('<input type="color" name="tipos_color[new_'+idx+'][color]" value="#000000" />');
        row.append('<input type="color" name="tipos_color[new_'+idx+'][text]" value="#ffffff" />');
        row.append('<label><input type="checkbox" name="tipos_color[new_'+idx+'][delete]" value="1" /> '+cdbMensajes.eliminar+'</label>');
        $('#cdb-tipos-color').append(row);
    });

    // Marcar fila como eliminada
    $('#cdb-tipos-color').on('change', 'input[type="checkbox"]', function(){
        $(this).closest('.cdb-tipo-color-row').toggleClass('deleting', this.checked);
    });
});
