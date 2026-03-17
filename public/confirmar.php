<?php
/* public/confirmar.php */
include_once __DIR__ . '/../api/main.php';

$mensaje_error = "Token no válido o ya utilizado.";
$exito = false;

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Intentamos activar al usuario
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = 1, token_verificacion = NULL WHERE token_verificacion = ? AND activo = 0");
    $stmt->bind_param("s", $token);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: login.php?success=activated");
        exit;
    } else {
        $mensaje_error = "El enlace de activación ha expirado o ya ha sido utilizado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación - CMS BASE</title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body style="display:flex; align-items:center; min-height:100vh;">
    <div class="caja txt-centro">
        <div style="font-size: 3rem; margin-bottom: 10px;">⚠️</div>
        <h1>Ups...</h1>
        <div class="alerta alerta-error" style="margin-bottom: 20px;">
            <?php echo $mensaje_error; ?>
        </div>
        <p style="font-size: 0.9rem; color: #666; margin-bottom: 25px;">
            Si crees que esto es un error, por favor contacta con el administrador o intenta registrarte de nuevo.
        </p>
        <a href="login.php" class="boton">Ir al Inicio de Sesión</a>
    </div>
</body>
</html>