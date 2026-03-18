<?php
/* public/login.php */
include_once __DIR__ . '/../api/main.php';

if (sesion_activa()) {
    header("Location: ../admin/admin.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // La función ya debe estar disponible gracias al require_once en main.php
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $email = limpiar_entrada($_POST['email']); // Cambiado a email
        $pass = $_POST['pass'];

        if (login($email, $pass)) {
            header("Location: ../admin/admin.php");
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    } else {
        $error = "Error de seguridad (CSRF). Recarga la página.";
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
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 25px;">Introduce tu correo electrónico</p>
        </div>

        <?php if ($error): ?>
            <div class="alerta alerta-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label>Correo Electrónico</label>
            <input type="email" name="email" class="campo" placeholder="tu@email.com" required autofocus>

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