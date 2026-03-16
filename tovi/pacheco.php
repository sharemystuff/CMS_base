<?php
/* tovi/pacheco.php */
define('INSTALACION_PERMITIDA', true);

require_once __DIR__ . '/../seguridad/funciones.php';
require_once __DIR__ . '/funciones.php';

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
        
        $master_salt = bin2hex(random_bytes(32));
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $url_detectada = $protocol . "://" . $_SERVER['HTTP_HOST'];

        // Opciones Críticas de Seguridad
        create_opcion('salt_key', $master_salt);
        create_opcion('url_sitio', $url_detectada); 
        create_opcion('recuerdame', '30'); 
        create_opcion('registro', '0'); 
        create_opcion('mailer_host', '');
        create_opcion('mailer_username', '');
        create_opcion('mailer_password', '');
        create_opcion('mailer_port', '465');

        // Generar api/db.php
        $contenido_db = "<?php\nmysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);\n";
        $contenido_db .= "try {\n    \$conexion = new mysqli('{$datos_db['host']}', '{$datos_db['user']}', '{$datos_db['pass']}', '{$datos_db['name']}');\n";
        $contenido_db .= "    \$conexion->set_charset('utf8mb4');\n} catch (Exception \$e) { header('Location: /tovi/pacheco.php'); exit; }\n";
        
        file_put_contents(__DIR__ . '/../api/db.php', $contenido_db);
        $fase = 2;
    } else { $error = "❌ Error de conexión."; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    include_once __DIR__ . '/../api/db.php';
    if (create_user_admin(limpiar_entrada($_POST['admin_nombre']), 'admin', limpiar_entrada($_POST['admin_email']), 'admin', $_POST['admin_pass'])) {
        $fase = 3;
    } else { $error = "❌ Error al crear admin."; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS BASE - Instalación Limpia</title>
    <style>
        body { font-family: sans-serif; background: #121212; color: #eee; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e1e1e; padding: 30px; border-radius: 8px; border: 1px solid #333; width: 350px; }
        h1 { color: #1db954; text-align: center; font-size: 1.5rem; }
        input { width: 100%; padding: 10px; margin: 10px 0; background: #222; border: 1px solid #444; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #1db954; border: none; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h1>INSTALADOR</h1>
        <?php if ($fase == 1): ?>
            <form method="POST">
                <input type="text" name="db_host" value="localhost">
                <input type="text" name="db_user" placeholder="Usuario DB" required>
                <input type="password" name="db_pass" placeholder="Password DB">
                <input type="text" name="db_name" placeholder="Nombre DB" required>
                <button type="submit" name="instalar_db">INSTALAR</button>
            </form>
        <?php elseif ($fase == 2): ?>
            <form method="POST">
                <input type="text" name="admin_nombre" placeholder="Tu nombre" required>
                <input type="email" name="admin_email" placeholder="Tu email" required>
                <input type="password" name="admin_pass" placeholder="Password Admin" required>
                <button type="submit" name="crear_admin">CREAR CUENTA</button>
            </form>
        <?php else: ?>
            <div style="text-align:center;">
                <p>✅ Instalación Exitosa.</p>
                <a href="../public/login.php" style="color:#1db954;">Ir al Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>