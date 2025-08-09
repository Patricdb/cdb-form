(function(){
  document.addEventListener('DOMContentLoaded', function(){
    document
      .querySelectorAll('.cdb-niveles--bienvenida .cdb-niveles__fill')
      .forEach(function(el){
        void el.offsetWidth; // forzar reflow antes de animar
        el.classList.add('is-in');
      });
  });
})();

