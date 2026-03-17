<?php
/* tovi/pacheco.php */

define('INSTALACION_PERMITIDA', true);

// REPARACIÓN QUIRÚRGICA: Cargamos funciones antes que nada para evitar el "Undefined function"
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/../seguridad/funciones.php';

// Ahora cargamos main.php para la lógica de estado
require_once __DIR__ . '/../api/main.php';

$error = ""; 
$fase = 1; 

// DETECCIÓN INTELIGENTE DE FASE (Cerebro de Pacheco)
if (isset($conexion) && !$conexion->connect_error) {
    $estado_actual = get_opcion('estado');
    
    if ($estado_actual === 'instalando') {
        $fase = 2; // Inmune al F5
    } elseif ($estado_actual === 'live') {
        header('Location: /public/index.php');
        exit;
    }
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$url_sugerida = $protocol . "://" . $_SERVER['HTTP_HOST'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar_db'])) {
    $datos_db = [
        'host' => $_POST['db_host'], 
        'user' => $_POST['db_user'], 
        'pass' => $_POST['db_pass'], 
        'name' => $_POST['db_name']
    ];
    
    $url_sitio = rtrim($_POST['sitio_url'], '/'); 
    $resultado_conn = pacheco_instalar($datos_db);

    if ($resultado_conn) {
        $conexion = $resultado_conn;
        $master_salt = bin2hex(random_bytes(32));

        create_opcion('salt_key', $master_salt);
        create_opcion('url_sitio', $url_sitio); 
        create_opcion('recuerdame', '30'); 
        create_opcion('registro', '0'); 
        create_opcion('mailer_host', '');
        create_opcion('mailer_username', '');
        create_opcion('mailer_password', '');
        create_opcion('mailer_port', '465');

        $contenido_config = "<?php\n";
        $contenido_config .= "// Configuración de CMS BASE generada por Pacheco\n";
        $contenido_config .= "\$DB_DATOS = [\n";
        $contenido_config .= "    'host' => '{$datos_db['host']}',\n";
        $contenido_config .= "    'user' => '{$datos_db['user']}',\n";
        $contenido_config .= "    'pass' => '{$datos_db['pass']}',\n";
        $contenido_config .= "    'name' => '{$datos_db['name']}'\n";
        $contenido_config .= "];\n";
        
        file_put_contents(__DIR__ . '/../api/config.php', $contenido_config);
        
        header("Location: pacheco.php");
        exit;
    } else { $error = "❌ Error de conexión a la base de datos."; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    if (isset($conexion)) {
        $nombre = limpiar_entrada($_POST['admin_nombre']);
        $email = limpiar_entrada($_POST['admin_email']);
        $pass = $_POST['admin_pass'];

        if (create_user_admin($nombre, 'admin', $email, 'admin', $pass)) {
            update_opcion('estado', 'live');
            $fase = 3;
        } else { $error = "❌ Error al crear la cuenta de administrador."; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="/assets/images/iconos/favicon.ico">
    <title>CMS BASE - Instalación</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #eee; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e1e1e; padding: 30px; border-radius: 12px; border: 1px solid #333; width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #F2C94C; text-align: center; margin-bottom: 20px; }
        label { display: block; font-size: 0.8rem; color: #888; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 5px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #7A006C; }
        button { width: 100%; padding: 12px; background: #7A006C; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; color: #fff; transition: 0.3s; }
        button:hover { background: #9C1A8E; }
        .error-msg { color: #ff5555; font-size: 0.8rem; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <div style="text-align:center; margin-bottom:10px;"><img src="/assets/images/icons/logo.svg" width="60" alt="Logo"></div>
        <h1>CMS BASE</h1>
        <?php if ($fase == 1): ?>
            <form method="POST">
                <label>URL del Sitio</label>
                <input type="text" name="sitio_url" value="<?php echo $url_sugerida; ?>" required>
                <label>Host DB</label>
                <input type="text" name="db_host" value="localhost">
                <label>Usuario DB</label>
                <input type="text" name="db_user" placeholder="ej: root" required>
                <label>Password DB</label>
                <input type="password" name="db_pass">
                <label>Nombre DB</label>
                <input type="text" name="db_name" required>
                <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
                <button type="submit" name="instalar_db">CONFIGURAR NÚCLEO</button>
            </form>
        <?php elseif ($fase == 2): ?>
            <form method="POST">
                <p style="text-align:center; color:#bbb;">Base de datos lista. Crea el Administrador.</p>
                <label>Nombre Completo</label>
                <input type="text" name="admin_nombre" required>
                <label>Email Admin</label>
                <input type="email" name="admin_email" required>
                <label>Password Admin</label>
                <input type="password" name="admin_pass" required>
                <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
                <button type="submit" name="crear_admin">FINALIZAR INSTALACIÓN</button>
            </form>
        <?php else: ?>
            <div style="text-align:center; padding: 20px;">
                <h2 style="color:#F2C94C;">✅ ¡Despegue exitoso!</h2>
                <p>Configuración completada correctamente.</p>
                <a href="/public/login.php" style="color:#F2C94C; font-weight:bold; text-decoration:none;">Ir al Login →</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>