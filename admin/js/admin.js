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

    // --- LÓGICA DE PERFIL (CROPPER) ---
    const $inputAvatar = $('#inputAvatar');
    const $modal = $('#modalCropper');
    const $imageToCrop = $('#imageToCrop');
    let cropper;

    // 1. Click en la foto simula click en el input file
    $('.perfil-avatar-wrapper').on('click', function() {
        $inputAvatar.click();
    });

    // 2. Al seleccionar archivo
    $inputAvatar.on('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            const file = files[0];
            if (/^image\/\w+$/.test(file.type)) {
                const url = URL.createObjectURL(file);
                $imageToCrop.attr('src', url);
                $modal.css('display', 'flex').hide().fadeIn();
                
                // Destruir instancia previa si existe
                if (cropper) cropper.destroy();
                
                // Inicializar Cropper
                const image = document.getElementById('imageToCrop');
                cropper = new Cropper(image, {
                    aspectRatio: 1,
                    viewMode: 1,
                    autoCropArea: 1,
                });
                $inputAvatar.val(''); // Limpiar input para permitir re-selección
            } else {
                alert('Por favor selecciona una imagen válida.');
            }
        }
    });

    // 3. Cancelar
    $('#btnCancelarCrop').on('click', function() {
        $modal.fadeOut();
        if (cropper) cropper.destroy();
    });

    // 4. Aceptar y Recortar
    $('#btnAceptarCrop').on('click', function() {
        const canvas = cropper.getCroppedCanvas({ width: 600, height: 600 }); // Estandarizamos a 600px
        const base64 = canvas.toDataURL('image/jpeg', 0.9);

        // Enviar AJAX
        $.post('admin-ajax.php', {
            action: 'subir_imagen_perfil',
            imagen: base64,
            csrf_token: CMS_VARS.csrf_token
        }, function(resp) {
            if (resp.status === true) {
                $('#imgPerfilActual').attr('src', '../' + resp.url + '?t=' + new Date().getTime()); // Cache buster
                $('.user-profile .avatar').attr('src', '../' + resp.url + '?t=' + new Date().getTime()); // Header avatar
                $modal.fadeOut();
            } else {
                alert('Error: ' + resp.msg);
            }
        }, 'json');
    });
});