<?php
/* public/login.php */
include_once __DIR__ . '/../api/main.php';

if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

$error = "";
$ip_actual = get_client_ip();
$intentos = contar_intentos_fallidos($ip_actual);
$limite_intentos = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $intentos < $limite_intentos) {
    if (!isset($_POST['csrf_token']) || !validarCSRF($_POST['csrf_token'])) {
        die("Sesión inválida.");
    }
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    $stmt = $conexion->prepare("SELECT id, nombre, nickname, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($user['activo'] != 1) {
            $error = "Cuenta inactiva. Revisa tu email.";
        } 
        elseif (password_verify($pass, $user['password'])) {
            limpiar_intentos_ip($ip_actual);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];
            header("Location: ../admin/admin.php");
            exit;
        } else {
            registrar_intento_fallido($ip_actual, $email);
            $error = "Credenciales incorrectas.";
        }
    } else {
        registrar_intento_fallido($ip_actual, $email);
        $error = "Credenciales incorrectas.";
    }
    sleep(1); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - CMS BASE</title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body style="display:flex; align-items:center; min-height:100vh;">
    <div class="caja">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="60" alt="Logo">
        </div>
        <h1>CMS BASE</h1>
        <?php if($error): ?><div class="alerta alerta-error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label>Email</label>
            <input type="email" name="email" class="campo" required <?php if($intentos >= $limite_intentos) echo 'disabled'; ?>>
            <label>Contraseña</label>
            <input type="password" name="password" class="campo" required <?php if($intentos >= $limite_intentos) echo 'disabled'; ?>>
            <button type="submit" class="boton" <?php if($intentos >= $limite_intentos) echo 'disabled'; ?>>ENTRAR</button>
        </form>
        <p class="txt-centro" style="margin-top:20px; font-size:0.8rem;">
            <a href="recuperar.php" class="enlace">¿Olvidaste tu contraseña?</a>
        </p>
    </div>
</body>
</html>