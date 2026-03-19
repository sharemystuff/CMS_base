/* admin/js/admin.js */
$(document).ready(function() {
    
    // Switcher de Modo Noche
    let debounceModo; // Variable para controlar el temporizador

    $('#btnModo').on('click', function() {
        // 1. Cambio visual inmediato (Optimistic UI)
        $('body').toggleClass('modo-noche');
        const activo = $('body').hasClass('modo-noche') ? 'oscuro' : 'claro';
        
        // 2. Persistencia en Cookie (para carga rápida al refrescar)
        document.cookie = "modo_oscuro=" + activo + "; path=/; max-age=" + (60*60*24*30);

        // 3. Persistencia en DB con Debounce (evita saturar el servidor)
        clearTimeout(debounceModo);
        debounceModo = setTimeout(function() {
            $.post('admin-ajax.php', {
                action: 'guardar_modo',
                modo: activo,
                csrf_token: CMS_VARS.csrf_token
            }, function(resp) {
                if(resp.status === 'error') console.warn('Error guardando modo:', resp.message);
            }, 'json');
        }, 1000); // Espera 1 segundo tras el último click para guardar
    });

    // Animación simple de menú
    $('.menu-item').hover(function() {
        $(this).find('i').addClass('animated pulse');
    }, function() {
        $(this).find('i').removeClass('animated pulse');
    });
});