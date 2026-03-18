/* admin/js/admin.js */
$(document).ready(function() {
    // Switcher de Modo Noche
    $('#btnModo').on('click', function() {
        $('body').toggleClass('modo-noche');
        const activo = $('body').hasClass('modo-noche') ? '1' : '0';
        document.cookie = "modo_oscuro=" + activo + "; path=/; max-age=" + (60*60*24*30);
    });

    // Animación simple de menú
    $('.menu-item').hover(function() {
        $(this).find('i').addClass('animated pulse');
    }, function() {
        $(this).find('i').removeClass('animated pulse');
    });
});