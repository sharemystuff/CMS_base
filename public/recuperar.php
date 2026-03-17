<?php
/* public/recuperar.php */
include_once __DIR__ . '/../api/main.php';

if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

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
                $enlace = url_base() . "/public/reset.php?token=" . $token;
                $logo_url = url_base() . "/assets/images/iconos/logo.svg";
                $asunto = "Restablecer contraseña - " . (get_opcion('nombre_sitio') ?? 'CMS BASE');
                
                $cuerpo = "
                <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; text-align: center;'>
                    <img src='{$logo_url}' width='80' alt='Logo' style='margin-bottom: 20px;'>
                    <h2 style='color: #7A006C;'>Recuperación de cuenta</h2>
                    <p>Has solicitado restablecer tu contraseña. Por seguridad, este enlace <strong>expirará en 5 minutos</strong>.</p>
                    <div style='margin: 30px 0;'>
                        <a href='{$enlace}' style='background: #7A006C; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>RESTABLECER CONTRASEÑA</a>
                    </div>
                    <p style='font-size: 0.8rem; color: #999;'>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                </div>";

                enviar_email($email, $asunto, $cuerpo);
            }
            
            $mensaje = "Si el correo existe, recibirás un enlace válido por 5 minutos.";
            $tipo_alerta = "exito";
        } else {
            $mensaje = "Ingresa un correo válido.";
            $tipo_alerta = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acceso - CMS BASE</title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body class="flex-center">
    <div class="caja login-box animated fadeIn">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="60" alt="Logo" class="logo-anim">
            <h1>Recuperar Acceso</h1>
            <p class="txt-muted">Se enviará un enlace temporal.</p>
        </div>
        <?php if ($mensaje): ?>
            <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($tipo_alerta !== 'exito'): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="grupo-campo">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="campo" required autofocus placeholder="tu@email.com">
            </div>
            <button type="submit" class="boton btn-block">ENVIAR ENLACE</button>
        </form>
        <?php endif; ?>
        <div class="txt-centro mt-20">
            <a href="login.php" class="enlace">← Volver al login</a>
        </div>
    </div>
</body>
</html>