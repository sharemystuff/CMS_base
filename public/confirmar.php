<?php
/* public/confirmar.php */
include_once __DIR__ . '/../api/main.php';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = limpiar_entrada($_GET['token']);
    
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = 1, token_verificacion = NULL WHERE token_verificacion = ?");
    $stmt->bind_param("s", $token);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // Redirigimos al login con aviso de éxito
        header("Location: login.php?success=activated");
        exit;
    }
}

// Si llega aquí sin token o token inválido, mostramos error
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error de Activación</title>
    <style>
        body { font-family: sans-serif; background: #0f0f0f; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: #181818; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #333; }
        a { color: #1db954; text-decoration: none; border: 1px solid #1db954; padding: 10px 20px; border-radius: 30px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color:#ff5555;">Token no válido o ya utilizado.</h2>
        <p>No pudimos activar tu cuenta. Intenta solicitar un nuevo registro.</p>
        <a href="login.php">Volver al Inicio</a>
    </div>
</body>
</html>