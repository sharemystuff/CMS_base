<?php
/* public/confirmar.php */
include_once __DIR__ . '/../api/main.php';

$mensaje = "Verificando tu cuenta...";
$tipo_alerta = "exito";
$token = limpiar_entrada($_GET['token'] ?? '');

if ($token) {
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = 1, token_verificacion = NULL WHERE token_verificacion = ? AND activo = 0");
    $stmt->bind_param("s", $token);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $mensaje = "¡Cuenta activada con éxito! Ya puedes ingresar.";
    } else {
        $mensaje = "El enlace no es válido o ya fue utilizado.";
        $tipo_alerta = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body class="contenedor-auth">
    <div class="auth-card animated fadeIn txt-centro">
        <div style="font-size: 4rem; margin-bottom: 20px;">✨</div>
        <h2>Activación</h2>
        <div class="f-alerta f-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <a href="login.php" class="f-boton" style="text-decoration:none; display:inline-block;">IR AL LOGIN</a>
    </div>
</body>
</html>