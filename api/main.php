<?php
/* api/main.php */

// 1. CONFIGURACIÓN DE SESIÓN INTELIGENTE
if (session_status() === PHP_SESSION_NONE) {
    $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $is_local = (strpos($host, 'localhost') !== false || strpos($host, '.mahg') !== false);

    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => ($is_local && !$is_secure) ? false : true, 
        'cookie_samesite' => 'Lax',
    ]);
}

// 2. GENERACIÓN DE TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. CARGA DE LIBRERÍAS CORE
require_once __DIR__ . '/../tovi/funciones.php';      
require_once __DIR__ . '/../seguridad/funciones.php';  

// 4. DETERMINAR ENTORNO
$script_actual = $_SERVER['PHP_SELF'];
$es_instalador = (strpos($script_actual, 'pacheco.php') !== false);
$path_config = __DIR__ . '/config.php';

// 5. CONEXIÓN A BASE DE DATOS
if (file_exists($path_config)) {
    require_once $path_config;
    
    // Conexión silenciada para manejo personalizado
    $conexion = @new mysqli($DB_DATOS['host'], $DB_DATOS['user'], $DB_DATOS['pass'], $DB_DATOS['name']);
    
    if ($conexion->connect_error) {
        if (!$es_instalador) {
            die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>Error de Conexión</h1><p>No se pudo conectar a la base de datos. Verifica tu config.php</p></div>");
        }
    } else {
        $conexion->set_charset("utf8mb4");

        // 6. CARGA DEL MODELO (Funciones de DB y Mailer)
        if (file_exists(__DIR__ . '/funciones_model.php')) {
            require_once __DIR__ . '/funciones_model.php';
        }

        // 7. VALIDACIÓN DE ESTADO DEL SISTEMA
        $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
        if ($check_table && $check_table->num_rows > 0) {
            $OPC = obtener_todas_las_opciones(); 
            $estado = leer_opcion('estado');
            
            if ($estado === 'instalando' && !$es_instalador) {
                header("Location: " . url_base() . "/tovi/pacheco.php");
                exit;
            }
        }
    }
} else {
    // Si no hay config y no estamos en el instalador, vamos a Pacheco
    if (!$es_instalador) {
        header("Location: " . url_base() . "/tovi/pacheco.php");
        exit;
    }
}