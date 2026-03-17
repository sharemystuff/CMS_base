<?php
/* public/login.php */
include_once __DIR__ . '/../api/main.php';

// Si ya está logueado, al panel
if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

$error = "";

// Procesar Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí es donde fallaba: validamos que la función exista antes de llamarla
    // o nos aseguramos de que seguridad/funciones.php esté cargado en main.php
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $user = limpiar_entrada($_POST['user']);
        $pass = $_POST['pass'];

        if (login($user, $pass)) {
            header("Location: ../admin/admin.php");
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $error = "Error de validación de seguridad (CSRF).";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - CMS BASE</title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body style="display:flex; align-items:center; min-height:100vh;">

    <div class="caja">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="60" alt="Logo" style="margin-bottom:10px;">
            <h1>Iniciar Sesión</h1>
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 25px;">Accede al panel administrativo</p>
        </div>

        <?php if ($error): ?>
            <div class="alerta alerta-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success']) && $_GET['success'] === 'activated'): ?>
            <div class="alerta alerta-exito">¡Cuenta activada! Ya puedes iniciar sesión.</div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label>Nickname o Email</label>
            <input type="text" name="user" class="campo" placeholder="pelin_dev" required autofocus>

            <label>Contraseña</label>
            <input type="password" name="pass" class="campo" placeholder="••••••••" required>

            <button type="submit" class="boton">ENTRAR AL SISTEMA</button>
        </form>

        <div class="txt-centro" style="margin-top: 25px;">
            <a href="recuperar.php" class="enlace" style="font-size: 0.85rem;">¿Olvidaste tu contraseña?</a>
            <hr style="border: 0; border-top: 1px solid var(--ui); margin: 20px 0;">
            <p style="font-size: 0.85rem; color: #666;">
                ¿No tienes cuenta? <a href="registro.php" class="enlace">Regístrate</a>
            </p>
        </div>
    </div>

</body>
</html>