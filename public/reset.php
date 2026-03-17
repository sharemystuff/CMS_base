<?php
/* public/reset.php */
include_once __DIR__ . '/../api/main.php';

if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

$mensaje = "";
$tipo_alerta = "";
$token_valido = false;
$user_data = null;

$token_url = limpiar_entrada($_GET['token'] ?? '');

if ($token_url) {
    $user_data = validar_token_recuperacion($token_url);
    if ($user_data) {
        $token_valido = true;
    } else {
        $mensaje = "El enlace es inválido o ha expirado (recuerda que solo dura 5 min).";
        $tipo_alerta = "error";
    }
} else {
    header("Location: login.php");
    exit;
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
            if (actualizar_password_recuperada($token_url, $pass1)) {
                header("Location: login.php?msg=reset_success");
                exit;
            } else {
                $mensaje = "Error al actualizar.";
                $tipo_alerta = "error";
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
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body class="flex-center">
    <div class="caja login-box">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="60" alt="Logo">
            <h1>Nueva Contraseña</h1>
            <p class="txt-muted">Estás actualizando la cuenta:<br><strong><?php echo e($user_data['email'] ?? ''); ?></strong></p>
        </div>
        <?php if ($mensaje): ?>
            <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if ($token_valido): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="grupo-campo">
                <label>Nueva Contraseña</label>
                <input type="password" name="pass1" class="campo" required placeholder="Escribe tu nueva clave">
            </div>
            <div class="grupo-campo">
                <label>Confirmar Nueva Contraseña</label>
                <input type="password" name="pass2" class="campo" required placeholder="Repítela aquí">
            </div>
            <button type="submit" class="boton btn-block mt-20">ACTUALIZAR Y ENTRAR</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>