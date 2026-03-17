<?php
/* tovi/pacheco.php */

// --- BLOQUEO DE RE-INSTALACIÓN ---
// Si el archivo de base de datos ya existe, redirigimos al inicio inmediatamente.
if (file_exists(__DIR__ . '/../api/db.php')) {
    header('Location: ../index.php');
    exit;
}

define('INSTALACION_PERMITIDA', true);

require_once __DIR__ . '/../seguridad/funciones.php';
require_once __DIR__ . '/funciones.php';

$error = ""; 
$fase = 1; 

// Detectamos la URL actual para sugerirla en el input
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
        global $conexion; 
        $conexion = $resultado_conn;
        
        $master_salt = bin2hex(random_bytes(32));

        // Opciones Críticas de Seguridad
        create_opcion('salt_key', $master_salt);
        create_opcion('url_sitio', $url_sitio); 
        create_opcion('recuerdame', '30'); 
        create_opcion('registro', '0'); 
        create_opcion('mailer_host', '');
        create_opcion('mailer_username', '');
        create_opcion('mailer_password', '');
        create_opcion('mailer_port', '465');

        // Generar api/db.php con blindaje contra acceso directo
        $contenido_db = "<?php\n";
        $contenido_db .= "// Archivo generado automáticamente por Pacheco\n";
        $contenido_db .= "mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);\n";
        $contenido_db .= "try {\n";
        $contenido_db .= "    \$conexion = new mysqli('{$datos_db['host']}', '{$datos_db['user']}', '{$datos_db['pass']}', '{$datos_db['name']}');\n";
        $contenido_db .= "    \$conexion->set_charset('utf8mb4');\n";
        $contenido_db .= "} catch (Exception \$e) {\n";
        $contenido_db .= "    // Si falla la conexión, solo entonces permitimos ver el instalador\n";
        $contenido_db .= "    header('Location: /tovi/pacheco.php'); exit;\n";
        $contenido_db .= "}\n";
        
        file_put_contents(__DIR__ . '/../api/db.php', $contenido_db);
        $fase = 2;
    } else { $error = "❌ Error de conexión a la base de datos."; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    include_once __DIR__ . '/../api/db.php';
    if (create_user_admin(limpiar_entrada($_POST['admin_nombre']), 'admin', limpiar_entrada($_POST['admin_email']), 'admin', $_POST['admin_pass'])) {
        $fase = 3;
    } else { $error = "❌ Error al crear la cuenta de administrador."; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS BASE - Instalación</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #eee; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e1e1e; padding: 30px; border-radius: 12px; border: 1px solid #333; width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #1db954; text-align: center; margin-bottom: 20px; }
        label { display: block; font-size: 0.8rem; color: #888; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; background: #2a2a2a; border: 1px solid #444; color: #fff; border-radius: 5px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #1db954; }
        button { width: 100%; padding: 12px; background: #1db954; border: none; font-weight: bold; cursor: pointer; border-radius: 5px; color: #000; transition: 0.3s; }
        button:hover { background: #1ed760; }
        .error-msg { color: #ff5555; font-size: 0.8rem; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        
        <?php if ($fase == 1): ?>
            <form method="POST">
                <label>URL del Sitio (sin barra final)</label>
                <input type="text" name="sitio_url" value="<?php echo $url_sugerida; ?>" required>
                
                <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">
                
                <label>Host DB</label>
                <input type="text" name="db_host" value="localhost">
                <label>Usuario DB</label>
                <input type="text" name="db_user" placeholder="ej: root" required>
                <label>Password DB</label>
                <input type="password" name="db_pass" placeholder="contraseña de mysql">
                <label>Nombre DB</label>
                <input type="text" name="db_name" placeholder="ej: cms_base" required>
                
                <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
                <button type="submit" name="instalar_db">CONTINUAR</button>
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
                <button type="submit" name="crear_admin">FINALIZAR INSTALACIÓN</button>
            </form>

        <?php else: ?>
            <div style="text-align:center; padding: 20px;">
                <h2 style="color:#1db954;">✅ ¡Listo!</h2>
                <p>El CMS ha sido configurado correctamente.</p>
                <a href="../public/login.php" style="color:#1db954; font-weight:bold; text-decoration:none;">Ir al Login →</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>