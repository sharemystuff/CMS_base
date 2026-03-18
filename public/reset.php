<?php
/* public/reset.php */
include_once __DIR__ . '/../api/main.php';

$mensaje = "";
$tipo_alerta = "";
$token_valido = false;
$user_data = null;

$token_url = limpiar_entrada($_GET['token'] ?? '');

if ($token_url) {
    // Restauramos tu función de validación (debe estar en el modelo)
    if (function_exists('validar_token_recuperacion')) {
        $user_data = validar_token_recuperacion($token_url);
        if ($user_data) $token_valido = true;
    }
}

if (!$token_valido) {
    $mensaje = "El enlace es inválido o ha expirado.";
    $tipo_alerta = "error";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';

        if (strlen($pass1) < 8) {
            $mensaje = "Mínimo 8 caracteres.";
            $tipo_alerta = "error";
        } elseif ($pass1 !== $pass2) {
            $mensaje = "Las contraseñas no coinciden.";
            $tipo_alerta = "error";
        } else {
            // Llamamos a tu función de actualización
            if (cambiar_password_por_token($token_url, $pass1)) {
                $mensaje = "Clave actualizada. Ya puedes iniciar sesión.";
                $tipo_alerta = "exito";
                $token_valido = false; // Ocultar formulario
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body class="contenedor-auth">
    <div class="auth-card animated fadeIn">
        <h2>Nueva Contraseña</h2>
        <?php if ($mensaje): ?>
            <div class="f-alerta f-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($token_valido): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label style="font-weight:700; font-size:0.8rem; color:#888;">NUEVA CONTRASEÑA</label>
            <input type="password" name="pass1" class="f-campo" required>
            <label style="font-weight:700; font-size:0.8rem; color:#888;">REPETIR CONTRASEÑA</label>
            <input type="password" name="pass2" class="f-campo" required>
            <button type="submit" class="f-boton">ACTUALIZAR CLAVE</button>
        </form>
        <?php else: ?>
            <div style="text-align:center; margin-top:20px;">
                <a href="login.php" class="f-boton" style="text-decoration:none; display:inline-block;">IR AL LOGIN</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>