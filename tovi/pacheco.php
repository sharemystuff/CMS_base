<?php
/* tovi/pacheco.php */

define('INSTALACION_PERMITIDA', true);
include_once __DIR__ . '/../seguridad/funciones.php';
include_once __DIR__ . '/funciones.php';

$error = "";
$fase = 1; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar_db'])) {
    $datos_db = [
        'host' => $_POST['db_host'],
        'user' => $_POST['db_user'],
        'pass' => $_POST['db_pass'],
        'name' => $_POST['db_name']
    ];

    $resultado_conn = pacheco_instalar($datos_db);

    if ($resultado_conn) {
        global $conexion;
        $conexion = $resultado_conn;
        
        // Insertamos la opción de duración de sesión por defecto (30 días)
        create_opcion('recuerdame', '30'); 

        $contenido_db = "<?php\n/* api/db.php */\n\$host = '{$datos_db['host']}';\n\$user = '{$datos_db['user']}';\n\$pass = '{$datos_db['pass']}';\n\$db   = '{$datos_db['name']}';\n\n\$conexion = @new mysqli(\$host, \$user, \$pass, \$db);\nif (\$conexion->connect_error) { die('Error: ' . \$conexion->connect_error); }\n\$conexion->set_charset('utf8mb4');\n";
        
        if (file_put_contents(__DIR__ . '/../api/db.php', $contenido_db)) {
            $fase = 2;
        } else {
            $error = "❌ Error al escribir api/db.php";
        }
    } else {
        $error = "❌ Error de conexión MySQL.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    include_once __DIR__ . '/../api/db.php';
    $nombre = limpiar_entrada($_POST['admin_nombre']);
    $email  = limpiar_entrada($_POST['admin_email']);
    $pass   = $_POST['admin_pass'];
    if (email_valido($email)) {
        $user_id = create_user($nombre, 'admin', $email, 'admin', $pass);
        if ($user_id) { $fase = 3; } else { $error = "❌ Error al crear admin."; }
    } else { $error = "❌ Email inválido."; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pacheco Installer - CMS BASE</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #e0e0e0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e1e1e; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; border: 1px solid #333; }
        h1 { color: #1db954; text-align: center; }
        label { display: block; margin: 10px 0 5px; font-size: 0.8rem; }
        input { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #333; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #1db954; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; margin-top: 20px; }
        .success { background: #1b3321; color: #88ffaa; padding: 20px; border-radius: 6px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        <?php if ($fase == 1): ?>
            <form method="POST">
                <label>Host</label><input type="text" name="db_host" value="localhost" required>
                <label>Usuario</label><input type="text" name="db_user" required>
                <label>Password</label><input type="password" name="db_pass">
                <label>Nombre DB</label><input type="text" name="db_name" required>
                <button type="submit" name="instalar_db">CONFIGURAR DB</button>
            </form>
        <?php elseif ($fase == 2): ?>
            <form method="POST">
                <label>Nombre</label><input type="text" name="admin_nombre" required>
                <label>Email</label><input type="email" name="admin_email" required>
                <label>Password</label><input type="password" name="admin_pass" required>
                <button type="submit" name="crear_admin">FINALIZAR INSTALACIÓN</button>
            </form>
        <?php elseif ($fase == 3): ?>
            <div class="success"><h3>✅ ¡Instalado!</h3><a href="../public/login.php" style="color:#1db954">Ir al Login</a></div>
        <?php endif; ?>
    </div>
</body>
</html>