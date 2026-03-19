<?php
/* tovi/pacheco.php */
define('INSTALACION_PERMITIDA', true);

require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/../seguridad/funciones.php';
require_once __DIR__ . '/../api/main.php';

// Asegurar sesión para CSRF incluso si main.php no la inició por falta de config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = ""; 
$fase = 1; 
$config_existe = file_exists(__DIR__ . '/../api/config.php');

// LÓGICA DE BLOQUEO: Si hay config, no permitimos volver a la fase 1
if ($config_existe) {
    if (!isset($conexion) || $conexion->connect_error) {
        die("<div style='font-family:sans-serif;text-align:center;padding:50px;color:#333;'><h1>Error de Conexión</h1><p>El sistema está instalado (existe config.php) pero no conecta a la base de datos.</p><p>Para reinstalar, elimine manualmente <b>api/config.php</b>.</p></div>");
    }
    
    $estado_actual = leer_opcion('estado'); // <--- Actualizado
    
    if ($estado_actual === 'instalando') {
        $fase = 2;
    } elseif ($estado_actual === 'live') {
        header('Location: ' . url_base() . '/public/index.php');
        exit;
    }
}

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$url_sugerida = $protocol . "://" . e($_SERVER['HTTP_HOST'] ?? 'localhost');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar_db'])) {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        $error = "Error de seguridad (Token CSRF inválido).";
    } elseif ($config_existe) {
        $error = "El sistema ya está configurado. Elimine config.php para reinstalar.";
    } else {
        $datos_db = [
            'host' => limpiar_entrada($_POST['db_host']), 
            'user' => limpiar_entrada($_POST['db_user']), 
            'pass' => $_POST['db_pass'], // Contraseña tal cual
            'name' => limpiar_entrada($_POST['db_name'])
        ];

        if (pacheco_instalar($datos_db)) {
            // Intentamos conectar inmediatamente para configurar opciones iniciales
            $conexion = new mysqli($datos_db['host'], $datos_db['user'], $datos_db['pass'], $datos_db['name']);
            
            guardar_opcion('url_sitio', limpiar_entrada($_POST['url_sitio'])); // <--- Actualizado
            guardar_opcion('estado', 'instalando');         // <--- Actualizado
            
            header("Refresh:0");
            exit;
        } else {
            $error = "Error: No se pudo conectar o crear la base de datos. Verifique credenciales.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        $error = "Error de seguridad (CSRF).";
    } elseif ($fase !== 2) {
        $error = "Fase incorrecta.";
    } else {
        $nombre = limpiar_entrada($_POST['admin_nombre']);
        $email  = limpiar_entrada($_POST['admin_email']);
        $pass   = $_POST['admin_pass'];
        
        if (crear_usuario_admin($nombre, 'admin', $email, 'admin', $pass)) {
            guardar_opcion('estado', 'live'); // <--- Actualizado
            $fase = 3;
        } else {
            $error = "Error al crear el usuario administrador.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pacheco - Instalador CMS BASE</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
    <style>
        body { background: var(--oscuro); color: #fff; font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .inst-card { background: #fff; color: #333; padding: 40px; border-radius: 20px; width: 100%; max-width: 450px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); }
        h1 { font-size: 1.5rem; color: var(--primario); text-align: center; }
        .campo { width: 100%; padding: 12px; margin: 10px 0 20px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .boton { width: 100%; padding: 15px; background: var(--primario); color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="inst-card">
        <?php if($fase == 1): ?>
            <h1>Configuración DB</h1>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <input type="text" name="db_host" class="campo" value="localhost">
                <input type="text" name="db_user" class="campo" value="root">
                <input type="password" name="db_pass" class="campo" placeholder="Password DB">
                <input type="text" name="db_name" class="campo" placeholder="Nombre DB">
                <input type="text" name="url_sitio" class="campo" value="<?php echo $url_sugerida; ?>">
                <button type="submit" name="instalar_db" class="boton">INSTALAR</button>
            </form>
        <?php elseif($fase == 2): ?>
            <h1>Admin inicial</h1>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <input type="text" name="admin_nombre" class="campo" placeholder="Nombre">
                <input type="email" name="admin_email" class="campo" placeholder="Email">
                <input type="password" name="admin_pass" class="campo" placeholder="Password">
                <button type="submit" name="crear_admin" class="boton">FINALIZAR</button>
            </form>
        <?php else: ?>
            <div style="text-align:center;">
                <h2>✅ ¡Listo!</h2>
                <a href="<?php echo url_base(); ?>/public/login.php" class="boton" style="text-decoration:none; display:block;">IR AL LOGIN</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>