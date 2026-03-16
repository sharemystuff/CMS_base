<?php
/* tovi/pacheco.php */
define('INSTALACION_PERMITIDA', true);
include_once __DIR__ . '/../seguridad/funciones.php';
include_once __DIR__ . '/funciones.php';

$error = ""; $fase = 1; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar_db'])) {
    $datos_db = ['host'=>$_POST['db_host'], 'user'=>$_POST['db_user'], 'pass'=>$_POST['db_pass'], 'name'=>$_POST['db_name']];
    $resultado_conn = pacheco_instalar($datos_db);

    if ($resultado_conn) {
        global $conexion; $conexion = $resultado_conn;
        create_opcion('salt_key', generar_salt());
        create_opcion('recuerdame', '30'); 
        create_opcion('registro', '1');
        create_opcion('mailer_host', '');
        create_opcion('mailer_username', '');
        create_opcion('mailer_password', '');
        create_opcion('mailer_port', '');

        $contenido_db = "<?php\n/* api/db.php */\n\$host='{$datos_db['host']}'; \$user='{$datos_db['user']}'; \$pass='{$datos_db['pass']}'; \$db='{$datos_db['name']}';\n@\$conexion = new mysqli(\$host, \$user, \$pass, \$db);\nif(\$conexion->connect_error){ header('Location: /tovi/pacheco.php'); exit; }\n\$conexion->set_charset('utf8mb4');\n";
        file_put_contents(__DIR__ . '/../api/db.php', $contenido_db);
        $fase = 2;
    } else { $error = "❌ Error: Datos de conexión incorrectos."; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    include_once __DIR__ . '/../api/db.php';
    // El admin se crea ACTIVO (1) directamente
    if (create_user_admin($_POST['admin_nombre'], 'admin', $_POST['admin_email'], 'admin', $_POST['admin_pass'])) { 
        $fase = 3; 
    }
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
        label { display: block; margin-bottom: 5px; font-size: 0.8rem; color: #bbb; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #333; background: #2a2a2a; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #1db954; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        <?php if ($fase == 1): ?>
            <form method="POST">
                <label>Host</label><input type="text" name="db_host" value="localhost">
                <label>Usuario DB</label><input type="text" name="db_user">
                <label>Password DB</label><input type="password" name="db_pass">
                <label>Nombre DB</label><input type="text" name="db_name">
                <button type="submit" name="instalar_db">CONFIGURAR e INSTALAR</button>
            </form>
        <?php elseif ($fase == 2): ?>
            <form method="POST">
                <label>Nombre Admin</label><input type="text" name="admin_nombre">
                <label>Email Admin</label><input type="email" name="admin_email">
                <label>Password Admin</label><input type="password" name="admin_pass">
                <button type="submit" name="crear_admin">FINALIZAR</button>
            </form>
        <?php else: ?>
            <div style="text-align:center"><h3>✅ ¡Instalado!</h3><a href="../public/login.php" style="color:#1db954">Ir al Login</a></div>
        <?php endif; ?>
    </div>
</body>
</html>