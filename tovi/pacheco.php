<?php
/* tovi/pacheco.php */

/**
 * CMS BASE - Instalador Visual "Pacheco"
 * Fase 2: Escritura de configuración y creación de usuario maestro.
 */

define('INSTALACION_PERMITIDA', true);

// Incluimos las librerías necesarias
include_once __DIR__ . '/../seguridad/funciones.php';
include_once __DIR__ . '/funciones.php';

$error = "";
$fase = 1; // 1: DB, 2: Usuario Admin, 3: Éxito final

// PROCESAMIENTO FASE 1: INSTALACIÓN DE DB
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar_db'])) {
    
    $datos_db = [
        'host' => $_POST['db_host'],
        'user' => $_POST['db_user'],
        'pass' => $_POST['db_pass'],
        'name' => $_POST['db_name']
    ];

    $resultado_conn = pacheco_instalar($datos_db);

    if ($resultado_conn) {
        // ESCRIBIMOS EL ARCHIVO api/db.php
        $contenido_db = "<?php\n"
                      . "/* api/db.php */\n"
                      . "// Archivo generado automáticamente por Pacheco\n"
                      . "\$host = '{$datos_db['host']}';\n"
                      . "\$user = '{$datos_db['user']}';\n"
                      . "\$pass = '{$datos_db['pass']}';\n"
                      . "\$db   = '{$datos_db['name']}';\n\n"
                      . "\$conexion = @new mysqli(\$host, \$user, \$pass, \$db);\n"
                      . "if (\$conexion->connect_error) { die('Error de Conexión: ' . \$conexion->connect_error); }\n"
                      . "\$conexion->set_charset('utf8mb4');\n";
        
        if (file_put_contents(__DIR__ . '/../api/db.php', $contenido_db)) {
            $fase = 2; // Pasamos a crear el usuario
        } else {
            $error = "❌ Error: No se pudo escribir en api/db.php. Revisa permisos de carpeta.";
        }
    } else {
        $error = "❌ Error: No se pudo conectar a MySQL con esos datos.";
    }
}

// PROCESAMIENTO FASE 2: CREACIÓN DE ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    // Incluimos la conexión que acabamos de crear
    include_once __DIR__ . '/../api/db.php';
    
    $nombre = limpiar_entrada($_POST['admin_nombre']);
    $email  = limpiar_entrada($_POST['admin_email']);
    $pass   = $_POST['admin_pass'];

    if (email_valido($email)) {
        // Usamos la función core que ya tenemos en tovi/funciones.php
        $user_id = create_user($nombre, 'admin', $email, 'admin', $pass);
        
        if ($user_id) {
            $fase = 3;
        } else {
            $error = "❌ No se pudo crear el usuario administrador.";
        }
    } else {
        $error = "❌ El email no es válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pacheco Installer - CMS BASE</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #e0e0e0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e1e1e; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid #333; }
        h1 { color: #1db954; text-align: center; margin-bottom: 5px; }
        p.subtitle { color: #888; text-align: center; font-size: 0.9rem; margin-bottom: 30px; }
        label { display: block; margin-bottom: 5px; font-size: 0.8rem; color: #bbb; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #333; background: #2a2a2a; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #1db954; border: none; color: #121212; font-weight: bold; cursor: pointer; border-radius: 4px; }
        .error { color: #ff5555; text-align: center; font-size: 0.85rem; margin-bottom: 15px; }
        .success { background: #1b3321; color: #88ffaa; padding: 20px; border-radius: 6px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        
        <?php if ($fase == 1): ?>
            <p class="subtitle">Fase 1: Base de Datos</p>
            <?php if ($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
            <form method="POST">
                <label>Host</label>
                <input type="text" name="db_host" value="localhost" required>
                <label>Usuario</label>
                <input type="text" name="db_user" required>
                <label>Password</label>
                <input type="password" name="db_pass">
                <label>Nombre DB</label>
                <input type="text" name="db_name" required>
                <button type="submit" name="instalar_db">CONFIGURAR DB</button>
            </form>

        <?php elseif ($fase == 2): ?>
            <p class="subtitle">Fase 2: Usuario Administrador</p>
            <?php if ($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>
            <form method="POST">
                <label>Tu Nombre</label>
                <input type="text" name="admin_nombre" placeholder="Ej: Pelín" required>
                <label>Email de Acceso</label>
                <input type="email" name="admin_email" placeholder="admin@sitio.com" required>
                <label>Contraseña Maestra</label>
                <input type="password" name="admin_pass" required>
                <button type="submit" name="crear_admin">CREAR CUENTA Y FINALIZAR</button>
            </form>

        <?php elseif ($fase == 3): ?>
            <div class="success">
                <h3>✅ ¡Todo listo!</h3>
                <p>El sistema se ha instalado correctamente.</p>
                <p>Ya puedes borrar la carpeta <b>tovi/</b> por seguridad.</p>
                <br>
                <a href="../public/login.php" style="color: #1db954; text-decoration: none; font-weight: bold;">IR AL LOGIN →</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>