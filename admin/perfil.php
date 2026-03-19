<?php
/* admin/perfil.php */
include_once __DIR__ . '/../api/main.php';
restringir_acceso(['admin', 'owner', 'user']);

$mensaje = "";

// 1. Procesar Formulario de Datos (Texto)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $nombre = limpiar_entrada($_POST['nombre']);
        $nickname = limpiar_entrada($_POST['nickname']);
        
        if (strlen($nombre) < 3) {
            $mensaje = "<div class='alerta alerta-error'>El nombre es muy corto.</div>";
        } else {
            if (actualizar_datos_usuario($_SESSION['user_id'], $nombre, $nickname)) {
                $_SESSION['user_nombre'] = $nombre; // Actualizamos sesión en caliente
                $mensaje = "<div class='alerta alerta-exito'>✅ Datos actualizados correctamente.</div>";
            } else {
                $mensaje = "<div class='alerta alerta-error'>❌ Error al actualizar los datos.</div>";
            }
        }
    } else {
        $mensaje = "<div class='alerta alerta-error'>❌ Error de seguridad (CSRF).</div>";
    }
}

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
    
    <?php echo $mensaje; ?>

    <div class="perfil-header">
        <!-- Avatar con Trigger -->
        <div class="perfil-avatar-wrapper" title="Cambiar foto">
            <img src="<?php echo $foto_perfil; ?>" alt="Avatar" id="imgPerfilActual">
            <div class="perfil-overlay">
                <i class="ti-camera" style="font-size: 2rem; margin-bottom: 5px;"></i>
                <span>Cambiar Foto</span>
            </div>
            <!-- Input oculto -->
            <input type="file" id="inputAvatar" accept="image/png, image/jpeg, image/jpg" style="display:none;">
        </div>

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

    <!-- Formulario de Edición -->
    <div class="form-card">
        <h3><i class="ti-pencil-alt"></i> Editar Datos Personales</h3>
        <form method="POST">
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