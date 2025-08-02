jQuery(document).ready(function($) {
    function cdbGetMsg(key){
        var src = (typeof cdbMsgs_i18n !== 'undefined' && cdbMsgs_i18n[key]) ? cdbMsgs_i18n[key] :
                  (typeof cdbMsgs !== 'undefined' && cdbMsgs[key] ? cdbMsgs[key] : '');
        return src.replace('|', ' ');
    }
    // 🔹 Manejo de actualización de disponibilidad del empleado
    $('#cdb-update-disponibilidad').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();


        $.ajax({
            type: 'POST',
            url: cdb_form_ajax.ajaxurl,
            data: formData + '&action=cdb_actualizar_disponibilidad',
            dataType: 'json',
            success: function(response) {

                if (response.success) {
                    window.alert(cdbGetMsg('cdb_ajax_disponibilidad_actualizada'));
                    location.reload();
                } else {
                    window.alert(response.data.message || cdbGetMsg('cdb_ajax_error_disponibilidad'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                window.alert(cdbGetMsg('cdb_ajax_error_disponibilidad'));
            }
        });
    });

    // 🔹 Manejo de actualización del estado del bar
    $('#cdb-update-estado-bar').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        var formData = form.serialize();


        $.ajax({
            type: 'POST',
            url: cdb_form_ajax.ajaxurl,
            data: formData + '&action=cdb_actualizar_estado_bar',
            dataType: 'json',
            success: function(response) {

                if (response.success) {
                    window.alert(cdbGetMsg('cdb_ajax_estado_bar_actualizado'));
                    location.reload();
                } else {
                    window.alert(response.data.message || cdbGetMsg('cdb_ajax_error_estado_bar'));
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                window.alert(cdbGetMsg('cdb_ajax_error_estado_bar'));
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
                window.alert(resp.data.message);
            }
            if (spinner) spinner.style.display = 'none';
        }).fail(function(jqXHR){
            if (spinner) spinner.style.display = 'none';
            window.alert(cdbGetMsg('cdb_ajax_error_comunicacion'));
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
            }, callback);
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
                window.alert(cdbGetMsg('cdb_ajax_error_anio_cifras'));
                return false;
            }
            if(nombreInput.value && !nombreInput.dataset.valid){
                window.alert(cdbGetMsg('cdb_ajax_error_nombre_invalido'));
                return false;
            }
            if(posInput.value && !posInput.dataset.valid){
                window.alert(cdbGetMsg('cdb_ajax_error_posicion_invalida'));
                return false;
            }
            if(barInput.value && !barInput.dataset.valid){
                window.alert(cdbGetMsg('cdb_ajax_error_bar_invalido'));
                return false;
            }
            if(anioInput.value && !anioInput.dataset.valid){
                window.alert(cdbGetMsg('cdb_ajax_error_anio_invalido'));
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

    // ---- Buscador de bares ----
    var bNombreInput, zonaInput, zonaIdInput, aperturaInput;
    var filtrarBarBtn, limpiarBarBtn;

    function cdbBuscarBares(){
        var spinner = document.getElementById('cdb-busqueda-bares-spinner');
        if (spinner) spinner.style.display = 'block';

        var params = {
            action: 'cdb_buscar_bares',
            nonce: cdb_form_ajax.nonce,
            nombre: bNombreInput.value,
            zona_id: zonaIdInput.value,
            apertura: aperturaInput.value
        };

        jQuery.getJSON(cdb_form_ajax.ajaxurl, params, function(resp){
            if(resp.success){
                jQuery('#cdb-busqueda-bares-resultados').html(resp.data.html);
            } else if (resp.data && resp.data.message){
                window.alert(resp.data.message);
            }
            if (spinner) spinner.style.display = 'none';
        }).fail(function(jqXHR){
            if (spinner) spinner.style.display = 'none';
            window.alert(cdbGetMsg('cdb_ajax_error_comunicacion'));
        });
    }

    function initBusquedaBares(){
        bNombreInput   = document.getElementById('cdb-bar-nombre');
        zonaInput      = document.getElementById('cdb-zona');
        zonaIdInput    = document.getElementById('cdb-zona-id');
        aperturaInput  = document.getElementById('cdb-apertura');
        filtrarBarBtn  = document.getElementById('cdb-bar-filtrar');
        limpiarBarBtn  = document.getElementById('cdb-bar-limpiar');

        if (!bNombreInput || !zonaInput || !aperturaInput) {
            return; // search form not present
        }

        if (!window.Awesomplete) {
            return false;
        }

        if (filtrarBarBtn) {
            filtrarBarBtn.addEventListener('click', function(){
                if (validarFiltrosBares()) {
                    cdbBuscarBares();
                }
            });
        }

        if (limpiarBarBtn) {
            limpiarBarBtn.addEventListener('click', function(){
                bNombreInput.value = '';
                zonaInput.value = '';
                aperturaInput.value = '';
                zonaIdInput.value = '';
                bNombreInput.dataset.valid = '';
                zonaInput.dataset.valid = '';
            cdbBuscarBares();
            });
        }

        function obtenerSugs(t, term, cb){
            jQuery.getJSON(cdb_form_ajax.ajaxurl, {
                action: 'cdb_sugerencias',
                nonce: cdb_form_ajax.nonce,
                tipo: t,
                term: term
            }, cb);
        }

        var zonaS = [], barS = [];

        var awBarNombre = new Awesomplete(bNombreInput, {minChars:1, autoFirst:true});
        bNombreInput.addEventListener('input', function(){
            bNombreInput.dataset.valid = '';
            obtenerSugs('bar', this.value, function(res){ barS = res; awBarNombre.list = res.map(function(r){ return r.label; }); });
        });
        bNombreInput.addEventListener('awesomplete-selectcomplete', function(){
            bNombreInput.dataset.valid = '1';
        });

        var awZona = new Awesomplete(zonaInput, {minChars:1, autoFirst:true});
        zonaInput.addEventListener('input', function(){
            zonaInput.dataset.valid = '';
            zonaIdInput.value = '';
            obtenerSugs('zona', this.value, function(res){ zonaS = res; awZona.list = res.map(function(r){ return r.label; }); });
        });
        zonaInput.addEventListener('awesomplete-selectcomplete', function(){
            var val = zonaInput.value;
            var obj = zonaS.find(function(i){ return i.label === val; });
            if(obj){ zonaIdInput.value = obj.id; zonaInput.dataset.valid = '1'; }
        });

        [bNombreInput, zonaInput, aperturaInput].forEach(function(el){
            el.addEventListener('keydown', function(e){
                if(e.key === 'Enter'){ e.preventDefault(); if(filtrarBarBtn) filtrarBarBtn.click(); }
            });
        });

        function validarFiltrosBares(){
            if(aperturaInput.value && !/^[0-9]{4}$/.test(aperturaInput.value)){
                window.alert(cdbGetMsg('cdb_ajax_error_anio_cifras'));
                return false;
            }
            if(bNombreInput.value && !bNombreInput.dataset.valid){
                window.alert(cdbGetMsg('cdb_ajax_error_bar_invalido'));
                return false;
            }
            if(zonaInput.value && !zonaInput.dataset.valid){
                window.alert(cdbGetMsg('cdb_ajax_error_zona_invalida'));
                return false;
            }
            return true;
        }

        cdbBuscarBares();

        return true;
    }

    var initBars = initBusquedaBares();
    if (initBars === false) {
        document.addEventListener('awesomplete-loaded', initBusquedaBares, { once: true });
        setTimeout(initBusquedaBares, 50);
    }
});

