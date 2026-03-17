<?php
/* public/confirmar.php */
include_once __DIR__ . '/../api/main.php';

$mensaje_error = "Token no válido o ya utilizado.";

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = limpiar_entrada($_GET['token']);
    
    // Intentamos activar al usuario
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = 1, token_verificacion = NULL WHERE token_verificacion = ? AND activo = 0");
    $stmt->bind_param("s", $token);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // ¡Éxito! Redirigimos al login
        header("Location: login.php?success=activated");
        exit;
    } else {
        // Si no hubo filas afectadas, el token no existe o la cuenta ya estaba activa
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
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #181818; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #333; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h2 { color: #ff5555; margin-top: 0; }
        p { color: #888; line-height: 1.5; }
        .btn { 
            color: #000; 
            background: #1db954; 
            text-decoration: none; 
            padding: 12px 25px; 
            border-radius: 30px; 
            display: inline-block; 
            margin-top: 25px; 
            font-weight: bold;
            transition: 0.3s;
        }
        .btn:hover { background: #1ed760; transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="card">
        <h2>⚠️ Ups...</h2>
        <p><?php echo $mensaje_error; ?></p>
        <p>Si crees que esto es un error, por favor contacta con el administrador o intenta registrarte de nuevo.</p>
        <a href="login.php" class="btn">Ir al Inicio de Sesión</a>
    </div>
</body>
</html>