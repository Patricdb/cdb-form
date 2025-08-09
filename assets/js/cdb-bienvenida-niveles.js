(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.cdb-niveles__fill').forEach(function(el){
      // Forzar reflujo y activar animación
      void el.offsetWidth;
      el.classList.add('is-in');
    });
  });
})();
