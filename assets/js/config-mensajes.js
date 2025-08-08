jQuery(document).ready(function($){

    if (typeof cdbMensajes !== 'object') return;

    var nuevoNombre   = cdbMensajes.nuevoNombre || '';
    var nuevaClase    = cdbMensajes.nuevaClase || '';
    var eliminar      = cdbMensajes.eliminar || '';
    var contrasteBajo = cdbMensajes.contrasteBajo || '';
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
        var bg    = opt.data('bg');
        var texto = opt.data('text');
        var cls   = opt.data('class');
        var bcol  = opt.data('bordercolor');
        var bwid  = opt.data('borderwidth');
        var brad  = opt.data('borderradius');
        var cont  = $(this).closest('.cdb-config-mensaje');
        var prev  = cont.find('.cdb-mensaje-preview');
        prev.removeClass().addClass('cdb-aviso cdb-mensaje-preview').addClass(cls);
        prev.css({
            'background-color': bg,
            'color': texto,
            'border': bwid+' solid '+bcol,
            'border-radius': brad
        });
        if (parseFloat(bwid) === 0) {
            prev.css('border-left', '4px solid '+bcol);
        } else {
            prev.css('border-left', '');
        }
        cont.find('.cdb-clase-css').text(cls);
    }).trigger('change');

    // Añadir nuevo tipo/color
    $('#cdb-add-tipo-color').on('click', function(e){
        e.preventDefault();
        var idx = $('#cdb-tipos-color .cdb-tipo-color-row').length + 1;
        var row = $('<div class="cdb-tipo-color-row"></div>');
        row.append('<span class="cdb-color-swatch"></span>');
        row.append('<input type="text" name="tipos_color[new_'+idx+'][name]" placeholder="'+nuevoNombre+'" />');
        row.append('<input type="text" name="tipos_color[new_'+idx+'][class]" placeholder="'+nuevaClase+'" />');
        row.append('<input type="color" name="tipos_color[new_'+idx+'][bg]" value="#000000" />');
        row.append('<input type="color" name="tipos_color[new_'+idx+'][text]" value="#ffffff" />');
        row.append('<input type="text" name="tipos_color[new_'+idx+'][border_color]" class="cdb-border-color" value="#000000" />');
        row.append('<input type="text" name="tipos_color[new_'+idx+'][border_width]" list="cdb-border-width" value="0px" />');
        row.append('<input type="text" name="tipos_color[new_'+idx+'][border_radius]" list="cdb-border-radius" value="4px" />');
        row.append('<label><input type="checkbox" name="tipos_color[new_'+idx+'][delete]" value="1" /> '+eliminar+'</label>');
        row.append('<span class="cdb-contrast-warning">'+contrasteBajo+'</span>');
        $('#cdb-tipos-color').append(row);
        row.find('.cdb-border-color').wpColorPicker();
    });

    // Marcar fila como eliminada
    $('#cdb-tipos-color').on('change', 'input[type="checkbox"]', function(){
        $(this).closest('.cdb-tipo-color-row').toggleClass('deleting', this.checked);
    });

    // Inicializar color picker en campos existentes
    $('.cdb-border-color').wpColorPicker();

    function hexToLuma(hex){
        hex = hex.replace('#','');
        if (hex.length === 3){ hex = hex.split('').map(function(h){ return h+h; }).join(''); }
        var r = parseInt(hex.substr(0,2),16)/255;
        var g = parseInt(hex.substr(2,2),16)/255;
        var b = parseInt(hex.substr(4,2),16)/255;
        var rs = r <= 0.03928 ? r/12.92 : Math.pow((r+0.055)/1.055,2.4);
        var gs = g <= 0.03928 ? g/12.92 : Math.pow((g+0.055)/1.055,2.4);
        var bs = b <= 0.03928 ? b/12.92 : Math.pow((b+0.055)/1.055,2.4);
        return 0.2126*rs + 0.7152*gs + 0.0722*bs;
    }
    function contrastRatio(bg, txt){
        var l1 = hexToLuma(bg)+0.05;
        var l2 = hexToLuma(txt)+0.05;
        return l1>l2 ? l1/l2 : l2/l1;
    }
    function updateContrast(row){
        var bg = row.find('input[name$="[bg]"]').val();
        var txt = row.find('input[name$="[text]"]').val();
        row.find('.cdb-color-swatch').css('background-color', bg);
        if (bg && txt){
            var ratio = contrastRatio(bg, txt);
            row.find('.cdb-contrast-warning').toggle(ratio < 4.5);
        }
    }
    $('#cdb-tipos-color').on('input change', 'input[name$="[bg]"], input[name$="[text]"]', function(){
        updateContrast($(this).closest('.cdb-tipo-color-row'));
    });
    $('#cdb-tipos-color .cdb-tipo-color-row').each(function(){ updateContrast($(this)); });

    // Controlar visibilidad de los avisos
    $('.cdb-mensaje-edicion input[data-role="mostrar"]').on('change', function(){
        var cont = $(this).closest('.cdb-config-mensaje');
        cont.toggleClass('oculto', !this.checked);
    }).trigger('change');
});
