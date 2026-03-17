<?php
/* public/recuperar.php */
include_once __DIR__ . '/../api/main.php';

// Seguridad: Si ya está logueado, fuera de aquí
if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

// Cabeceras Anti-Caché para máxima seguridad
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar CSRF
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $email = limpiar_entrada($_POST['email']);
        
        if (email_valido($email)) {
            // 2. Generar token (Función unificada en funciones_model.php)
            $token = generar_token_recuperacion($email);
            
            if ($token) {
                // AQUÍ SE ENVIARÍA EL EMAIL REAL CON PHPMailer
                // El link sería: url_base() . "/public/reset.php?token=" . $token
            }
            
            // SEGURIDAD: Respuesta genérica para evitar enumeración de usuarios
            $mensaje = "Si el correo está registrado, recibirás un enlace de recuperación en unos minutos.";
            $tipo_alerta = "exito";
        } else {
            $mensaje = "Por favor, ingresa un correo electrónico válido.";
            $tipo_alerta = "error";
        }
    } else {
        $mensaje = "Error de validación de seguridad. Intenta de nuevo.";
        $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body class="flex-center">

    <div class="caja login-box">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="60" alt="Logo">
            <h1>Recuperar Acceso</h1>
            <p class="txt-muted">Escribe tu email para restablecer tu contraseña.</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($tipo_alerta !== 'exito'): ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="grupo-campo">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="campo" placeholder="tu@email.com" required autofocus>
            </div>
            
            <button type="submit" class="boton btn-block">ENVIAR ENLACE</button>
        </form>
        <?php endif; ?>

        <div class="txt-centro mt-20">
            <a href="login.php" class="enlace">← Volver al inicio de sesión</a>
        </div>
    </div>

</body>
</html>