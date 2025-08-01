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
        var spinner = document.getElementById('cdb-busqueda-spinner');
        if (spinner) spinner.style.display = 'block';

        var params = {
            action: 'cdb_buscar_empleados',
            nonce: cdb_form_ajax.nonce,
            nombre: nombreInput.value,
            posicion_id: posIdInput.value,
            bar_id: barIdInput.value,
            anio: anioInput.value
        };

        jQuery.getJSON(cdb_form_ajax.ajaxurl, params, function(resp){
            if(resp.success){
                jQuery('#cdb-busqueda-empleados-resultados').html(resp.data.html);
            } else if (resp.data && resp.data.message) {
                alert(resp.data.message);
            }
            if (spinner) spinner.style.display = 'none';
        }).fail(function(jqXHR){
            if (spinner) spinner.style.display = 'none';
            alert('Error de comunicación');
            console.error('cdb_buscar_empleados AJAX fail', jqXHR);
        });
    }


    var nombreInput, posInput, posIdInput, barInput, barIdInput, anioInput;
    var filtrarBtn, limpiarBtn;

    function initBusqueda(){
        nombreInput   = document.getElementById('cdb-nombre');
        posInput      = document.getElementById('cdb-posicion');
        posIdInput    = document.getElementById('cdb-posicion-id');
        barInput      = document.getElementById('cdb-bar');
        barIdInput    = document.getElementById('cdb-bar-id');
        anioInput     = document.getElementById('cdb-anio');
        filtrarBtn    = document.getElementById('cdb-filtrar');
        limpiarBtn    = document.getElementById('cdb-limpiar');

        if (!nombreInput || !posInput || !barInput || !anioInput) {
            return; // search form not present
        }

        if (!window.Awesomplete) {
            return false;
        }

        if (filtrarBtn) {
            filtrarBtn.addEventListener('click', function(){
                if (validarFiltros()) {
                    cdbBuscarEmpleados();
                }
            });
        }

        if (limpiarBtn) {
            limpiarBtn.addEventListener('click', function(){
                nombreInput.value = '';
                posInput.value = '';
                barInput.value = '';
                anioInput.value = '';
                posIdInput.value = '';
                barIdInput.value = '';
                nombreInput.dataset.valid = '';
                posInput.dataset.valid = '';
                barInput.dataset.valid = '';
                anioInput.dataset.valid = '';
                cdbBuscarEmpleados();
            });
        }

        function obtenerSugerencias(tipo, termino, callback){
            jQuery.getJSON(cdb_form_ajax.ajaxurl, {
                action: 'cdb_sugerencias',
                nonce: cdb_form_ajax.nonce,
                tipo: tipo,
                term: termino
            }, callback).fail(function(jqXHR){
                console.error('cdb_sugerencias AJAX fail', jqXHR);
            });
        }

        var posSugs = [], barSugs = [];

        var awNombre  = new Awesomplete(nombreInput, { minChars:1, autoFirst:true });
        nombreInput.addEventListener('input', function(){
            nombreInput.dataset.valid = '';
            obtenerSugerencias('nombre', this.value, function(res){
                awNombre.list = res.map(function(r){ return r.label; });
            });
        });
        nombreInput.addEventListener('awesomplete-selectcomplete', function(){
            nombreInput.dataset.valid = '1';
        });

        var awPos = new Awesomplete(posInput, { minChars:1, autoFirst:true });
        posInput.addEventListener('input', function(){
            posInput.dataset.valid = '';
            posIdInput.value = '';
            obtenerSugerencias('posicion', this.value, function(res){
                posSugs = res;
                awPos.list = res.map(function(r){ return r.label; });
            });
        });
        posInput.addEventListener('awesomplete-selectcomplete', function(){
            var val = posInput.value;
            var obj = posSugs.find(function(i){ return i.label === val; });
            if(obj){
                posIdInput.value = obj.id;
                posInput.dataset.valid = '1';
            }
        });

        var awBar = new Awesomplete(barInput, { minChars:1, autoFirst:true });
        barInput.addEventListener('input', function(){
            barInput.dataset.valid = '';
            barIdInput.value = '';
            obtenerSugerencias('bar', this.value, function(res){
                barSugs = res;
                awBar.list = res.map(function(r){ return r.label; });
            });
        });
        barInput.addEventListener('awesomplete-selectcomplete', function(){
            var val = barInput.value;
            var obj = barSugs.find(function(i){ return i.label === val; });
            if(obj){
                barIdInput.value = obj.id;
                barInput.dataset.valid = '1';
            }
        });

        var awAnio = new Awesomplete(anioInput, { minChars:1, autoFirst:true });
        anioInput.addEventListener('input', function(){
            anioInput.dataset.valid = '';
            obtenerSugerencias('anio', this.value, function(res){
                awAnio.list = res.map(function(r){ return r.label; });
            });
        });
        anioInput.addEventListener('awesomplete-selectcomplete', function(){
            anioInput.dataset.valid = '1';
        });

        [nombreInput, posInput, barInput, anioInput].forEach(function(el){
            el.addEventListener('keydown', function(e){
                if(e.key === 'Enter'){
                    e.preventDefault();
                    if (filtrarBtn) filtrarBtn.click();
                }
            });
        });

        function validarFiltros(){
            if(anioInput.value && !/^[0-9]{4}$/.test(anioInput.value)){
                alert('El año debe tener 4 cifras');
                return false;
            }
            if(nombreInput.value && !nombreInput.dataset.valid){
                alert('Selecciona un nombre válido');
                return false;
            }
            if(posInput.value && !posInput.dataset.valid){
                alert('Selecciona una posición válida');
                return false;
            }
            if(barInput.value && !barInput.dataset.valid){
                alert('Selecciona un bar válido');
                return false;
            }
            if(anioInput.value && !anioInput.dataset.valid){
                alert('Selecciona un año válido');
                return false;
            }
            return true;
        }

        // Carga inicial
        cdbBuscarEmpleados();

        return true;
    }

    var initResult = initBusqueda();
    if (initResult === false) {
        document.addEventListener('awesomplete-loaded', initBusqueda, { once: true });
        setTimeout(initBusqueda, 50);
    }
});

