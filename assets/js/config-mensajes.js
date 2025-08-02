jQuery(document).ready(function($){
    // Toggle edición de mensaje
    $('.cdb-edit-mensaje').on('click', function(){
        var cont = $(this).closest('.cdb-config-mensaje');
        cont.toggleClass('editing');
        cont.find('.cdb-mensaje-edicion').toggle();
    });

    // Actualizar preview de texto
    $('.cdb-mensaje-edicion textarea').on('input', function(){
        $(this).closest('.cdb-config-mensaje').find('.cdb-mensaje-preview').text($(this).val());
    });

    // Actualizar preview de color y clase
    $('.cdb-mensaje-edicion select').on('change', function(){
        var opt   = $(this).find('option:selected');
        var color = opt.data('color');
        var cls   = opt.data('class');
        var cont  = $(this).closest('.cdb-config-mensaje');
        var prev  = cont.find('.cdb-mensaje-preview');
        prev.removeClass().addClass('cdb-mensaje-preview').addClass(cls);
        prev.css('border-left-color', color);
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
        row.append('<label><input type="checkbox" name="tipos_color[new_'+idx+'][delete]" value="1" /> '+cdbMensajes.eliminar+'</label>');
        $('#cdb-tipos-color').append(row);
    });

    // Marcar fila como eliminada
    $('#cdb-tipos-color').on('change', 'input[type="checkbox"]', function(){
        $(this).closest('.cdb-tipo-color-row').toggleClass('deleting', this.checked);
    });
});
