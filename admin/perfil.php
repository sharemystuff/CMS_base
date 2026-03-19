<?php
/* admin/perfil.php */
include_once __DIR__ . '/../api/main.php';
restringir_acceso(['admin', 'owner', 'user']);

$script_mensaje = ""; // Variable para inyectar JS

// 2. Obtener datos frescos
$u = obtener_datos_usuario($_SESSION['user_id']);
$foto_perfil = !empty($u['imagen']) ? recurso($u['imagen']) : recurso('admin/img/perfil.jpg');

// 3. Configuración de página
$page_config = [
    'titulo' => 'Mi Perfil',
    'menu_id' => 'perfil',
    'css' => ['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css'],
    'scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js']
];

include 'sec-header.php';
?>
<main class="animated fadeIn">
    
    <div class="perfil-header">
        <!-- Avatar con Trigger -->
        <div class="perfil-avatar-wrapper" title="Cambiar foto">
            <img src="<?php echo $foto_perfil; ?>" alt="Avatar" id="imgPerfilActual">
            <div class="perfil-overlay">
                <i class="ti-camera" style="font-size: 2rem; margin-bottom: 5px;"></i>
                <span>Cambiar Foto</span>
            </div>
        </div>
        <!-- Input oculto (Movido fuera para evitar recursión de eventos click) -->
        <input type="file" id="inputAvatar" accept="image/png, image/jpeg, image/jpg" style="display:none;">

        <!-- Info -->
        <div class="perfil-info">
            <h2><?php echo e($u['nombre']); ?></h2>
            <span class="badge" style="background:var(--primario); color:#fff; padding:3px 8px; border-radius:4px; font-size:0.8rem; text-transform:uppercase;"><?php echo e($u['rol']); ?></span>
            
            <div class="perfil-stats">
                <div class="stat-item">
                    <i class="ti-calendar"></i> Registrado: <strong><?php echo date('d/m/Y', strtotime($u['fecha'])); ?></strong>
                </div>
                <div class="stat-item">
                    <i class="ti-email"></i> <?php echo e($u['email']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación de Pestañas -->
    <div class="tabs-nav">
        <div class="tab-link active" data-tab="tab-datos">Datos Personales</div>
        <div class="tab-link" data-tab="tab-seguridad"><i class="ti-lock"></i> Seguridad</div>
    </div>

    <!-- TAB 1: Datos Personales -->
    <div id="tab-datos" class="tab-content active">
        <div class="form-card">
            <h3><i class="ti-pencil-alt"></i> Editar Datos Personales</h3>
            <form id="formDatos" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <label>Nombre Completo</label>
                <input type="text" name="nombre" class="campo" value="<?php echo e($u['nombre']); ?>" required>

                <label>Apodo / Nickname</label>
                <input type="text" name="nickname" class="campo" value="<?php echo e($u['nickname']); ?>">

                <button type="submit" name="actualizar_perfil" class="boton-p">
                    GUARDAR CAMBIOS
                </button>
            </form>
        </div>
    </div>

    <!-- TAB 2: Seguridad (Password) -->
    <div id="tab-seguridad" class="tab-content">
        <div class="form-card" style="border-left: 4px solid var(--secundario);">
            <h3><i class="ti-key"></i> Cambiar Contraseña</h3>
            <p style="color:var(--texto-suave); font-size:0.9rem; margin-bottom:20px;">
                Usa una contraseña segura. Se recomienda usar mayúsculas, símbolos y números.
            </p>
            
            <div class="pass-generator">
                <div id="passGenerada" class="pass-display no-copy">Generando...</div>
                <button type="button" id="btnRegenerar" class="btn-gen-action" title="Generar nueva"><i class="ti-reload"></i></button>
                <button type="button" id="btnUsarPass" class="btn-gen-action btn-use-pass">Usar esta contraseña</button>
            </div>
            <br>

            <form id="formPassword">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <label>Contraseña Actual (Requerido)</label>
                <div class="input-pass-wrapper">
                    <input type="password" name="pass_actual" class="campo" required>
                    <button type="button" class="btn-eye" tabindex="-1"><i class="ti-eye"></i></button>
                </div>

                <hr style="border:0; border-top:1px dashed var(--borde); margin: 20px 0;">

                <label>Nueva Contraseña</label>
                <div class="input-pass-wrapper">
                    <input type="password" id="passNueva" name="pass_nueva" class="campo" required minlength="8">
                    <button type="button" class="btn-eye" tabindex="-1"><i class="ti-eye"></i></button>
                </div>

                <label>Confirmar Nueva Contraseña</label>
                <div class="input-pass-wrapper">
                    <input type="password" id="passConfirm" class="campo" required onpaste="return false;">
                    <button type="button" class="btn-eye" tabindex="-1"><i class="ti-eye"></i></button>
                </div>

                <button type="submit" class="boton-p">
                    ACTUALIZAR CONTRASEÑA
                </button>
            </form>
        </div>
    </div>
</main>

<!-- Modal Cropper (Oculto) -->
<div id="modalCropper" class="modal-overlay">
    <div class="modal-content">
        <h3 style="color:var(--texto)">Ajustar Imagen</h3>
        <div class="cropper-container-wrapper">
            <img id="imageToCrop" src="">
        </div>
        <div class="modal-actions">
            <button type="button" id="btnCancelarCrop" class="btn-cancelar"><i class="ti-close"></i> Cancelar</button>
            <button type="button" id="btnAceptarCrop" class="btn-aceptar"><i class="ti-check"></i> Aceptar y Guardar</button>
        </div>
    </div>
</div>

<?php include 'sec-footer.php'; ?>