<?php
/* public/recuperar.php */
include_once __DIR__ . '/../api/main.php';

if (sesion_activa()) {
    header("Location: ../admin/admin.php");
    exit;
}

$mensaje = "";
$tipo_alerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $email = limpiar_entrada($_POST['email']);
        
        // Aquí se mantiene tu lógica: llamar a la función que genera el token y envía el mail
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Nota: Esta función debe estar en funciones_model.php o seguridad
            if (function_exists('mandar_correo_recuperacion') && mandar_correo_recuperacion($email)) {
                $mensaje = "Se ha enviado un enlace a tu correo.";
                $tipo_alerta = "exito";
            } else {
                $mensaje = "Si el correo existe, recibirás un enlace pronto.";
                $tipo_alerta = "exito"; // Por seguridad no confirmamos si existe o no
            }
        } else {
            $mensaje = "Email no válido.";
            $tipo_alerta = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Acceso - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo recurso('assets/css/estilos.css'); ?>">
</head>
<body class="contenedor-auth">
    <div class="auth-card animated fadeIn">
        <div style="text-align:center; margin-bottom:20px;">
            <img src="<?php echo recurso('assets/images/iconos/logo.svg'); ?>" width="50">
        </div>
        <h2>Recuperar Acceso</h2>
        <p style="text-align:center; color:#666; margin-bottom:30px;">Te enviaremos un enlace para restablecer tu clave.</p>

        <?php if ($mensaje): ?>
            <div class="f-alerta f-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($tipo_alerta !== 'exito'): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label style="font-weight:700; font-size:0.8rem; color:#888;">CORREO ELECTRÓNICO</label>
            <input type="email" name="email" class="f-campo" placeholder="tu@email.com" required autofocus>
            <button type="submit" class="f-boton">ENVIAR ENLACE</button>
        </form>
        <?php endif; ?>

        <div style="margin-top:20px; text-align:center;">
            <a href="login.php" style="color:#7A006C; font-weight:bold; text-decoration:none; font-size:0.9rem;">Volver al Login</a>
        </div>
    </div>
</body>
</html>