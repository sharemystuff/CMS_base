<?php
/* public/recuperar.php */
include_once __DIR__ . '/../api/main.php';

// Seguridad: Si ya está logueado, al admin.
if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

// Anti-caché para evitar volver atrás
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $email = limpiar_entrada($_POST['email']);
        
        if (email_valido($email)) {
            $token = generar_token_recuperacion($email);
            
            if ($token) {
                // AQUÍ IRÍA EL ENVÍO DE EMAIL (PHPMailer)
                // Por ahora simulamos que se envió.
                // $link = url_base() . "/public/reset.php?token=" . $token;
            }
            
            // SEGURIDAD: Siempre mostramos éxito aunque el email no exista
            $mensaje = "Si el correo está registrado, recibirás un enlace de recuperación en unos minutos.";
            $tipo_alerta = "exito";
        } else {
            $mensaje = "Por favor, ingresa un correo electrónico válido.";
            $tipo_alerta = "error";
        }
    } else {
        $mensaje = "Error de seguridad. Intenta de nuevo.";
        $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body style="display:flex; align-items:center; min-height:100vh;">

    <div class="caja">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="50" alt="Logo">
            <h1>Recuperar Clave</h1>
            <p style="font-size: 0.85rem; color: #666; margin-bottom: 20px;">
                Te enviaremos un enlace para restablecer tu acceso.
            </p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($tipo_alerta !== 'exito'): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <label>Tu Correo Electrónico</label>
            <input type="email" name="email" class="campo" placeholder="ejemplo@correo.com" required autofocus>
            
            <button type="submit" class="boton">ENVIAR INSTRUCCIONES</button>
        </form>
        <?php endif; ?>

        <div class="txt-centro" style="margin-top: 20px;">
            <a href="login.php" class="enlace" style="font-size: 0.85rem;">← Volver al login</a>
        </div>
    </div>

</body>
</html>