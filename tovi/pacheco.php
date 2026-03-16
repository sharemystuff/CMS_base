<?php
/* tovi/pacheco.php */
define('INSTALACION_PERMITIDA', true);

// Cargamos funciones antes de cualquier lógica
require_once __DIR__ . '/../seguridad/funciones.php';
require_once __DIR__ . '/funciones.php';

$error = ""; 
$fase = 1; 

// FASE 1: Configuración de Base de Datos y Opciones Iniciales
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
        
        // Generamos el Salt Maestro para esta instalación específica
        $master_salt = bin2hex(random_bytes(32));

        // Creación de Opciones Críticas
        create_opcion('salt_key', $master_salt);
        create_opcion('recuerdame', '30'); 
        create_opcion('registro', '0'); // Registro desactivado por defecto (Seguridad)
        create_opcion('mailer_host', '');
        create_opcion('mailer_username', '');
        create_opcion('mailer_password', '');
        create_opcion('mailer_port', '465');

        // Generación de api/db.php (Más limpio y con manejo de errores)
        $contenido_db = "<?php\n";
        $contenido_db .= "/* api/db.php - Generado por Pacheco Installer */\n\n";
        $contenido_db .= "\$host = '{$datos_db['host']}';\n";
        $contenido_db .= "\$user = '{$datos_db['user']}';\n";
        $contenido_db .= "\$pass = '{$datos_db['pass']}';\n";
        $contenido_db .= "\$db   = '{$datos_db['name']}';\n\n";
        $contenido_db .= "mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);\n";
        $contenido_db .= "try {\n";
        $contenido_db .= "    \$conexion = new mysqli(\$host, \$user, \$pass, \$db);\n";
        $contenido_db .= "    \$conexion->set_charset('utf8mb4');\n";
        $contenido_db .= "} catch (Exception \$e) {\n";
        $contenido_db .= "    if (strpos(\$_SERVER['PHP_SELF'], 'pacheco.php') === false) {\n";
        $contenido_db .= "        header('Location: /tovi/pacheco.php');\n";
        $contenido_db .= "        exit;\n";
        $contenido_db .= "    }\n";
        $contenido_db .= "}\n";
        
        file_put_contents(__DIR__ . '/../api/db.php', $contenido_db);
        
        $fase = 2;
    } else { 
        $error = "❌ Error: No se pudo conectar a la DB. Verifica los datos."; 
    }
}

// FASE 2: Creación del Administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    include_once __DIR__ . '/../api/db.php';
    
    $nombre = limpiar_entrada($_POST['admin_nombre']);
    $email  = limpiar_entrada($_POST['admin_email']);
    $pass   = $_POST['admin_pass']; // Contraseña plana para encodear
    
    // Usamos el nickname 'admin' por defecto para el primer usuario
    if (create_user_admin($nombre, 'admin', $email, 'admin', $pass)) { 
        $fase = 3; 
    } else {
        $error = "❌ Error al crear el usuario administrador.";
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
        .card { background: #1e1e1e; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #1db954; text-align: center; margin-bottom: 25px; }
        label { display: block; margin-bottom: 5px; font-size: 0.8rem; color: #888; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #333; background: #2a2a2a; color: #fff; border-radius: 4px; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: #1db954; background: #333; }
        button { width: 100%; padding: 12px; background: #1db954; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; color: #000; transition: 0.3s; margin-top: 10px; }
        button:hover { background: #1ed760; transform: translateY(-2px); }
        .success-box { text-align: center; background: #1b3321; padding: 20px; border-radius: 8px; border: 1px solid #1db954; }
        .error-msg { color: #ff5555; font-size: 0.8rem; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        
        <?php if ($fase == 1): ?>
            <form method="POST">
                <label>Host</label><input type="text" name="db_host" value="localhost">
                <label>Usuario DB</label><input type="text" name="db_user" placeholder="ej: root" required>
                <label>Password DB</label><input type="password" name="db_pass" placeholder="contraseña de mysql">
                <label>Nombre DB</label><input type="text" name="db_name" placeholder="ej: cms_base" required>
                <button type="submit" name="instalar_db">CONFIGURAR e INSTALAR</button>
                <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
            </form>

        <?php elseif ($fase == 2): ?>
            <form method="POST">
                <p style="font-size:0.9rem; color:#bbb; text-align:center;">Base de datos conectada. Crea tu cuenta de Administrador.</p>
                <label>Nombre Completo</label><input type="text" name="admin_nombre" required>
                <label>Email Admin</label><input type="email" name="admin_email" required>
                <label>Password Admin</label><input type="password" name="admin_pass" required>
                <button type="submit" name="crear_admin">FINALIZAR INSTALACIÓN</button>
                <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
            </form>

        <?php else: ?>
            <div class="success-box">
                <h3 style="color:#8fca9d; margin-top:0;">✅ ¡Todo listo!</h3>
                <p style="font-size:0.9rem;">Instalación completada. El registro público está desactivado.</p>
                <a href="../public/login.php" style="color:#1db954; font-weight:bold; text-decoration:none;">Ir al Login →</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>