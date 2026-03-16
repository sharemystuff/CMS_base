<?php
/* public/confirmar.php */
include_once __DIR__ . '/../api/main.php';

$mensaje = "Token no válido o expirado.";
$clase = "err";

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = limpiar_entrada($_GET['token']);
    
    // Buscamos el usuario con ese token y lo activamos
    $stmt = $conexion->prepare("UPDATE usuarios SET activo = 1, token_verificacion = NULL WHERE token_verificacion = ?");
    $stmt->bind_param("s", $token);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $mensaje = "✅ ¡Cuenta activada con éxito! Ya puedes iniciar sesión.";
        $clase = "ok";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activación de Cuenta</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #181818; padding: 40px; border-radius: 12px; text-align: center; border: 1px solid #333; max-width: 400px; }
        .ok { color: #1db954; }
        .err { color: #ff5555; }
        a { display: inline-block; margin-top: 20px; color: #1db954; text-decoration: none; font-weight: bold; border: 1px solid #1db954; padding: 10px 20px; border-radius: 30px; }
        a:hover { background: #1db954; color: #000; }
    </style>
</head>
<body>
    <div class="card">
        <h2 class="<?php echo $clase; ?>"><?php echo $mensaje; ?></h2>
        <a href="login.php">Ir al Inicio de Sesión</a>
    </div>
</body>
</html>