<?php
/* tovi/pacheco.php */

define('INSTALACION_PERMITIDA', true);

// REPARACIÓN QUIRÚRGICA: Cargamos funciones antes que nada
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
        header('Location: ' . url_base() . '/public/index.php');
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

    if (pacheco_instalar($datos_db)) {
        // Recargamos la conexión tras crear la DB y tablas
        $conexion = new mysqli($datos_db['host'], $datos_db['user'], $datos_db['pass'], $datos_db['name']);
        
        // Guardamos configuraciones iniciales usando set_opcion (Función correcta)
        set_opcion('url_sitio', $_POST['url_sitio']);
        set_opcion('estado', 'instalando');
        
        header("Refresh:0");
        exit;
    } else {
        $error = "Error: No se pudo conectar o crear la base de datos. Revisa los permisos.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_admin'])) {
    $nombre = $_POST['admin_nombre'];
    $email  = $_POST['admin_email'];
    $pass   = $_POST['admin_pass'];

    if (create_user_admin($nombre, 'admin', $email, 'admin', $pass)) {
        set_opcion('estado', 'live');
        $fase = 3;
    } else {
        $error = "Error al crear el usuario administrador.";
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
        .logo-inst { text-align: center; margin-bottom: 30px; }
        h1 { font-size: 1.5rem; color: var(--primario); margin-bottom: 20px; text-align: center; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9rem; }
        .campo { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .boton { width: 100%; padding: 15px; background: var(--primario); color: #fff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .boton:hover { background: var(--secundario); color: var(--oscuro); }
        .alerta { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .alerta-error { background: #fee; color: #c00; border: 1px solid #fcc; }
        .alerta-ok { background: #efe; color: #060; border: 1px solid #cfc; }
        .txt-centro { text-align: center; }
    </style>
</head>
<body>

    <div class="inst-card animated fadeIn">
        <div class="logo-inst">
            <img src="../assets/images/iconos/logo.svg" width="60" alt="Logo">
        </div>

        <?php if($fase == 1): ?>
            <h1>Configuración de DB</h1>
            <form method="POST">
                <label>Host de la DB</label>
                <input type="text" name="db_host" class="campo" value="localhost" required>
                
                <label>Usuario DB</label>
                <input type="text" name="db_user" class="campo" value="root" required>
                
                <label>Password DB</label>
                <input type="password" name="db_pass" class="campo" placeholder="Contraseña">
                
                <label>Nombre de la DB</label>
                <input type="text" name="db_name" class="campo" placeholder="cms_base" required>

                <label>URL del Sitio</label>
                <input type="text" name="url_sitio" class="campo" value="<?php echo $url_sugerida; ?>" required>
                
                <?php if($error): ?><div class="alerta alerta-error"><?php echo $error; ?></div><?php endif; ?>
                
                <button type="submit" name="instalar_db" class="boton">CONECTAR E INSTALAR</button>
            </form>

        <?php elseif($fase == 2): ?>
            <h1>Cuenta de Administrador</h1>
            <form method="POST">
                <div class="alerta alerta-ok">Base de datos lista. Crea el Administrador.</div>
                
                <label>Nombre Completo</label>
                <input type="text" name="admin_nombre" class="campo" placeholder="Tu nombre" required>
                
                <label>Email Admin</label>
                <input type="email" name="admin_email" class="campo" placeholder="email@admin.com" required>
                
                <label>Password Admin</label>
                <input type="password" name="admin_pass" class="campo" placeholder="Contraseña segura" required>
                
                <?php if($error): ?><div class="alerta alerta-error"><?php echo $error; ?></div><?php endif; ?>
                
                <button type="submit" name="crear_admin" class="boton">FINALIZAR INSTALACIÓN</button>
            </form>

        <?php else: ?>
            <div class="txt-centro" style="padding: 20px;">
                <h2 style="color:var(--secundario);">✅ ¡Despegue exitoso!</h2>
                <p style="margin: 20px 0; color: #666;">El núcleo de CMS BASE está operativo.</p>
                <a href="<?php echo url_base(); ?>/public/login.php" class="boton" style="text-decoration:none; display:block;">IR AL LOGIN →</a>
            </div>
        <?php endif; ?>
        
        <p class="txt-centro" style="margin-top:20px; font-size: 0.8rem; color: #999;">CMS BASE - Instalador Pacheco</p>
    </div>

</body>
</html>