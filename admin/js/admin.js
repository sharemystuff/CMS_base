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

    // --- FUNCIÓN DE MODAL REUTILIZABLE (Sustituye a alert) ---
    window.cms_mensaje = function(mensaje, tipo = 'exito') {
        let icono = tipo === 'error' ? 'ti-close tipo-error' : 'ti-check tipo-exito';
        let titulo = tipo === 'error' ? '¡Ups!' : '¡Hecho!';
        
        // Inyección DOM: Crear HTML solo si no existe
        if ($('#cmsModal').length === 0) {
            $('body').append(`
                <div id="cmsModal" class="modal-overlay" style="z-index: 2000;">
                    <div class="modal-content modal-mini animated bounceIn">
                        <div class="modal-icon"><i id="cmsModalIcon" class="${icono}"></i></div>
                        <h3 id="cmsModalTitle">${titulo}</h3>
                        <p id="cmsModalText" style="color:var(--texto);">${mensaje}</p>
                        <button class="modal-btn" onclick="$('#cmsModal').fadeOut()">ENTENDIDO</button>
                    </div>
                </div>
            `);
        } else {
            // Reutilizar: Actualizar contenido
            $('#cmsModalIcon').attr('class', icono);
            $('#cmsModalTitle').text(titulo);
            $('#cmsModalText').text(mensaje);
        }
        
        $('#cmsModal').css('display', 'flex').hide().fadeIn();
    };

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
                cms_mensaje('Por favor selecciona una imagen válida.', 'error');
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
                // Usamos la URL completa que nos devuelve PHP para evitar errores de rutas relativas
                $('#imgPerfilActual').attr('src', resp.full_url + '&t=' + new Date().getTime()); 
                $('.user-profile .avatar').attr('src', resp.full_url + '&t=' + new Date().getTime());
                $modal.fadeOut();
                cms_mensaje('Foto de perfil actualizada correctamente.', 'exito');
            } else {
                cms_mensaje('Error: ' + resp.msg, 'error');
            }
        }, 'json');
    });

    // --- PESTAÑAS (TABS) ---
    $('.tab-link').on('click', function() {
        const target = $(this).data('tab');
        
        // UI
        $('.tab-link').removeClass('active');
        $(this).addClass('active');
        
        // Content
        $('.tab-content').removeClass('active');
        $('#' + target).addClass('active');
    });

    // --- VER/OCULTAR PASSWORD ---
    $('.btn-eye').on('click', function(e) {
        e.preventDefault();
        const input = $(this).siblings('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('ti-eye').addClass('ti-close');
        } else {
            input.attr('type', 'password');
            icon.removeClass('ti-close').addClass('ti-eye');
        }
    });

    // --- CAMBIAR PASSWORD AJAX ---
    $('#formPassword').on('submit', function(e) {
        e.preventDefault();
        const p1 = $('#passNueva').val();
        const p2 = $('#passConfirm').val();
        
        if(p1 !== p2) {
            cms_mensaje('Las contraseñas nuevas no coinciden.', 'error');
            return;
        }
        
        $.post('admin-ajax.php', $(this).serialize() + '&action=cambiar_password', function(resp) {
            if(resp.status) {
                cms_mensaje(resp.msg, 'exito');
                $('#formPassword')[0].reset();
            } else {
                cms_mensaje('Error: ' + resp.msg, 'error');
            }
        }, 'json');
    });

    // --- ACTUALIZAR DATOS PERSONALES (AJAX) ---
    $('#formDatos').on('submit', function(e) {
        e.preventDefault();
        
        $.post('admin-ajax.php', $(this).serialize() + '&action=actualizar_perfil', function(resp) {
            if(resp.status) {
                cms_mensaje(resp.msg, 'exito');
                // Actualizar nombre en la interfaz sin recargar
                if(resp.nuevo_nombre) {
                    $('.user-profile .nombre').text(resp.nuevo_nombre);
                    $('.perfil-info h2').text(resp.nuevo_nombre);
                }
            } else {
                cms_mensaje(resp.msg, 'error');
            }
        }, 'json');
    });

    // --- GENERADOR DE CONTRASEÑAS SEGURAS ---
    function generarPassSegura() {
        const chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*+";
        // SEGURIDAD: Usamos crypto del navegador para entropía real
        const array = new Uint32Array(16);
        window.crypto.getRandomValues(array);
        
        let pass = "";
        for (let i = 0; i < 16; i++) {
            pass += chars.charAt(array[i] % chars.length);
        }
        $('#passGenerada').text(pass);
    }

    // Inicializar si existe el elemento
    if($('#passGenerada').length) {
        generarPassSegura();

        $('#btnRegenerar').on('click', function() {
            generarPassSegura();
        });

        $('#btnUsarPass').on('click', function() {
            const pass = $('#passGenerada').text();
            $('#passNueva, #passConfirm').val(pass).attr('readonly', true); // Llenar y bloquear
            $(this).text('¡Aplicada!'); // Feedback visual
        });
    }
});