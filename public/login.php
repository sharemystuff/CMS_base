<?php
/* public/login.php */
include_once __DIR__ . '/../api/main.php';

if (sesion_activa()) {
    header("Location: ../admin/admin.php");
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $email = limpiar_entrada($_POST['email']);
        $pass = $_POST['pass'];

        if (iniciar_sesion($email, $pass)) {
            header("Location: ../admin/admin.php");
            exit;
        } else {
            $error = "Correo o contraseña no válidos.";
        }
    } else {
        $error = "Fallo de seguridad (CSRF).";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo recurso('assets/css/estilos.css'); ?>">
</head>
<body class="contenedor-auth">

    <div class="auth-card animated fadeIn">
        <div style="text-align:center; margin-bottom:30px;">
            <img src="<?php echo recurso('assets/images/iconos/logo.svg'); ?>" width="50">
        </div>
        
        <h2>¡Hola de nuevo!</h2>
        <p style="text-align:center; color:#666; margin-bottom:30px;">Ingresa tus datos para continuar.</p>

        <?php if ($error): ?>
            <div class="f-alerta f-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label style="font-weight:700; font-size:0.8rem; color:#888;">CORREO ELECTRÓNICO</label>
            <input type="email" name="email" class="f-campo" placeholder="ejemplo@correo.com" required autofocus>

            <label style="font-weight:700; font-size:0.8rem; color:#888;">CONTRASEÑA</label>
            <input type="password" name="pass" class="f-campo" placeholder="••••••••" required>

            <button type="submit" class="f-boton">ENTRAR AL SISTEMA</button>
        </form>

        <div style="margin-top:30px; text-align:center; font-size:0.9rem;">
            ¿No tienes cuenta? <a href="registro.php" style="color:#7A006C; font-weight:700; text-decoration:none;">Regístrate aquí</a>
        </div>
    </div>

</body>
</html>