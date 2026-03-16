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
        
        // INSERTAMOS TODAS LAS OPCIONES DEL PUNTO 4
        create_opcion('recuerdame', '30'); 
        create_opcion('mailer_host', '');
        create_opcion('mailer_username', '');
        create_opcion('mailer_password', '');
        create_opcion('mailer_port', '');

        // Generación de api/db.php con redirección si falla la conexión
        $contenido_db = "<?php\n"
                      . "/* api/db.php */\n"
                      . "\$host = '{$datos_db['host']}';\n"
                      . "\$user = '{$datos_db['user']}';\n"
                      . "\$pass = '{$datos_db['pass']}';\n"
                      . "\$db   = '{$datos_db['name']}';\n\n"
                      . "@\$conexion = new mysqli(\$host, \$user, \$pass, \$db);\n\n"
                      . "if (\$conexion->connect_error) {\n"
                      . "    header('Location: /tovi/pacheco.php');\n"
                      . "    exit;\n"
                      . "}\n"
                      . "\$conexion->set_charset('utf8mb4');\n";
        
        if (file_put_contents(__DIR__ . '/../api/db.php', $contenido_db)) {
            $fase = 2;
        } else {
            $error = "❌ Error: Permisos insuficientes para crear api/db.php";
        }
    } else {
        $error = "❌ Error: Los datos de MySQL son incorrectos.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    include_once __DIR__ . '/../api/db.php';
    $nombre = limpiar_entrada($_POST['admin_nombre']);
    $email  = limpiar_entrada($_POST['admin_email']);
    $pass   = $_POST['admin_pass'];
    if (email_valido($email)) {
        $user_id = create_user($nombre, 'admin', $email, 'admin', $pass);
        if ($user_id) { $fase = 3; } else { $error = "❌ No se pudo crear el administrador."; }
    } else { $error = "❌ Email no válido."; }
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
            <p style="text-align:center; font-size: 0.8rem;">Instalación de Base de Datos</p>
            <?php if($error): ?><p style="color:#ff5555; font-size:0.8rem; text-align:center;"><?php echo $error; ?></p><?php endif; ?>
            <form method="POST">
                <label>Host</label><input type="text" name="db_host" value="localhost" required>
                <label>Usuario</label><input type="text" name="db_user" required>
                <label>Password</label><input type="password" name="db_pass">
                <label>Nombre DB</label><input type="text" name="db_name" required>
                <button type="submit" name="instalar_db">CONFIGURAR Y CREAR</button>
            </form>
        <?php elseif ($fase == 2): ?>
            <p style="text-align:center; font-size: 0.8rem;">Crear Usuario Maestro</p>
            <form method="POST">
                <label>Nombre Completo</label><input type="text" name="admin_nombre" required>
                <label>Email de Acceso</label><input type="email" name="admin_email" required>
                <label>Contraseña</label><input type="password" name="admin_pass" required>
                <button type="submit" name="crear_admin">FINALIZAR CONFIGURACIÓN</button>
            </form>
        <?php elseif ($fase == 3): ?>
            <div class="success"><h3>✅ Instalación Exitosa</h3><a href="../public/login.php" style="color:#1db954; font-weight:bold; text-decoration:none;">IR AL LOGIN →</a></div>
        <?php endif; ?>
    </div>
</body>
</html>