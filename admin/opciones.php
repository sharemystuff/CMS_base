<?php
/* admin/opciones.php */
include_once '../api/main.php';

// Verificamos si el usuario tiene permiso (Solo admin/owner)
if (!checking() || ($_SESSION['user_rol'] !== 'admin' && $_SESSION['user_rol'] !== 'owner')) {
    header('Location: ../public/login.php');
    exit;
}

$mensaje = "";

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_options'])) {
    
    // Validamos el token CSRF por seguridad
    if (isset($_POST['csrf_token']) && validarCSRF($_POST['csrf_token'])) {
        
        // Actualizamos cada opción. 
        // Usamos limpiar_entrada para todo excepto para la contraseña (por si tiene caracteres especiales)
        update_opcion('recuerdame', limpiar_entrada($_POST['recuerdame']));
        update_opcion('registro', isset($_POST['registro']) ? '1' : '0');
        update_opcion('mailer_host', limpiar_entrada($_POST['mailer_host']));
        update_opcion('mailer_username', limpiar_entrada($_POST['mailer_username']));
        update_opcion('mailer_password', $_POST['mailer_password']); 
        update_opcion('mailer_port', limpiar_entrada($_POST['mailer_port']));
        
        // Refrescamos el array global para que el formulario muestre los datos actualizados
        $OPC = get_all_opciones();
        $mensaje = "✅ Opciones actualizadas correctamente.";
        
    } else {
        $mensaje = "❌ Error de validación de seguridad (CSRF). Intente de nuevo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración - CMS BASE</title>
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Ajustes de legibilidad */
        .config-form h3 { color: #1db954; margin-top: 30px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .config-form label { display: block; color: #efefef; font-weight: bold; margin-bottom: 8px; font-size: 0.9rem; }
        .config-form input[type="text"], 
        .config-form input[type="number"], 
        .config-form input[type="password"] { 
            width: 100%; padding: 12px; margin-bottom: 20px; background: #252525; border: 1px solid #444; color: #fff; border-radius: 6px; box-sizing: border-box;
        }
        .config-form input:focus { border-color: #1db954; outline: none; }
        .alert { background: #1b3321; color: #88ffaa; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #1db954; }
    </style>
</head>
<body>
    <?php include_once 'sec-header.php'; ?>

    <div class="main-wrapper" style="display: flex;">
        <?php include_once 'sec-aside.php'; ?>

        <main class="content-area" style="padding: 40px; flex-grow: 1; background: #121212; color: #fff;">
            <h1>Configuración General</h1>
            
            <?php if($mensaje): ?>
                <div class="alert"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <form method="POST" class="config-form" style="max-width: 700px;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <h3>Seguridad y Acceso</h3>
                <label for="recuerdame">Duración de sesión (días):</label>
                <input type="number" name="recuerdame" id="recuerdame" value="<?php echo e($OPC['recuerdame'] ?? '30'); ?>">
                
                <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 30px; color: #1db954;">
                    <input type="checkbox" name="registro" <?php echo (($OPC['registro'] ?? '0') == '1') ? 'checked' : ''; ?> style="width:20px; height:20px; margin-right: 15px;">
                    ¿Permitir nuevos registros de usuarios?
                </label>

                <h3>Configuración de Correo (PHPMailer)</h3>
                <p style="color: #888; font-size: 0.8rem; margin-bottom: 20px;">Necesario para recuperación de contraseñas y notificaciones.</p>
                
                <label>Servidor SMTP (Host)</label>
                <input type="text" name="mailer_host" value="<?php echo e($OPC['mailer_host'] ?? ''); ?>" placeholder="ej: mail.tusitio.com">
                
                <label>Usuario (Email)</label>
                <input type="text" name="mailer_username" value="<?php echo e($OPC['mailer_username'] ?? ''); ?>" placeholder="ej: no-reply@tusitio.com">
                
                <label>Contraseña</label>
                <input type="password" name="mailer_password" value="<?php echo e($OPC['mailer_password'] ?? ''); ?>">
                
                <label>Puerto</label>
                <input type="text" name="mailer_port" value="<?php echo e($OPC['mailer_port'] ?? '465'); ?>" placeholder="465 (SSL) o 587 (TLS)">

                <button type="submit" name="save_options" style="background: #1db954; color: #000; padding: 15px 40px; border: none; border-radius: 30px; font-weight: bold; cursor: pointer; font-size: 1rem; margin-top: 20px;">
                    <i class="ti-save"></i> Guardar Cambios
                </button>
            </form>
        </main>
    </div>

    <?php include_once 'sec-footer.php'; ?>
</body>
</html>