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
        
        // Creamos las Salts únicas para esta instalación
        create_opcion('salt_key', generar_salt());
        create_opcion('recuerdame', '30'); 
        create_opcion('mailer_host', '');
        create_opcion('mailer_username', '');
        create_opcion('mailer_password', '');
        create_opcion('mailer_port', '');

        $contenido_db = "<?php\n/* api/db.php */\n\$host='{$datos_db['host']}'; \$user='{$datos_db['user']}'; \$pass='{$datos_db['pass']}'; \$db='{$datos_db['name']}';\n@\$conexion = new mysqli(\$host, \$user, \$pass, \$db);\nif(\$conexion->connect_error){ header('Location: /tovi/pacheco.php'); exit; }\n\$conexion->set_charset('utf8mb4');\n";
        file_put_contents(__DIR__ . '/../api/db.php', $contenido_db);
        $fase = 2;
    } else { $error = "❌ Error de conexión."; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    include_once __DIR__ . '/../api/db.php';
    if (create_user($_POST['admin_nombre'], 'admin', $_POST['admin_email'], 'admin', $_POST['admin_pass'])) { $fase = 3; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Pacheco Installer</title><style>body{font-family:sans-serif;background:#121212;color:#eee;display:flex;justify-content:center;align-items:center;height:100vh;} .card{background:#1e1e1e;padding:30px;border-radius:10px;border:1px solid #333;} input{display:block;width:100%;margin:10px 0;padding:10px;background:#2a2a2a;border:1px solid #444;color:#fff;} button{width:100%;padding:10px;background:#1db954;border:none;font-weight:bold;cursor:pointer;}</style></head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        <?php if($fase==1): ?>
            <form method="POST"><input type="text" name="db_host" value="localhost"><input type="text" name="db_user" placeholder="User"><input type="password" name="db_pass" placeholder="Pass"><input type="text" name="db_name" placeholder="DB Name"><button type="submit" name="instalar_db">INSTALAR</button></form>
        <?php elseif($fase==2): ?>
            <form method="POST"><input type="text" name="admin_nombre" placeholder="Nombre"><input type="email" name="admin_email" placeholder="Email"><input type="password" name="admin_pass" placeholder="Pass"><button type="submit" name="crear_admin">FINALIZAR</button></form>
        <?php else: ?>
            <div style="text-align:center"><h2>✅ ¡Listo!</h2><a href="../public/login.php" style="color:#1db954">Ir al Login</a></div>
        <?php endif; ?>
    </div>
</body>
</html>