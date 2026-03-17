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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <title>CMS BASE - Instalación</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body style="display:flex; align-items:center; min-height:100vh;">
    <div class="caja">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="60" alt="Logo" style="margin-bottom:10px;">
        </div>
        <h1>CMS BASE</h1>
        
        <?php if ($fase == 1): ?>
            <p class="txt-centro" style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">Fase 1: Configuración del Núcleo</p>
            <form method="POST">
                <label>URL del Sitio</label>
                <input type="text" name="sitio_url" class="campo" value="<?php echo $url_sugerida; ?>" required>
                
                <label>Host DB</label>
                <input type="text" name="db_host" class="campo" value="localhost">
                
                <label>Usuario DB</label>
                <input type="text" name="db_user" class="campo" placeholder="ej: root" required>
                
                <label>Password DB</label>
                <input type="password" name="db_pass" class="campo">
                
                <label>Nombre DB</label>
                <input type="text" name="db_name" class="campo" placeholder="Nombre de la base de datos" required>
                
                <?php if($error): ?><div class="alerta alerta-error"><?php echo $error; ?></div><?php endif; ?>
                
                <button type="submit" name="instalar_db" class="boton">CONFIGURAR NÚCLEO</button>
            </form>

        <?php elseif ($fase == 2): ?>
            <p class="txt-centro" style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">Fase 2: Cuenta Maestra</p>
            <form method="POST">
                <div class="alerta alerta-exito" style="text-align:center;">Base de datos lista. Crea el Administrador.</div>
                
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
        
        <p class="txt-centro" style="margin-top:20px; font-size: 0.7rem; color: #ccc; letter-spacing: 1px;">
            PACHECO INSTALLER v3.0
        </p>
    </div>
</body>
</html>