<?php
/* api/main.php */

// 1. CONFIGURACIÓN DE SESIÓN INTELIGENTE
if (session_status() === PHP_SESSION_NONE) {
    // Detectamos si es un entorno seguro (HTTPS)
    $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    
    // Detectamos si estamos en entorno local (XAMPP / .mahg)
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $is_local = (strpos($host, 'localhost') !== false || strpos($host, '.mahg') !== false);

    session_start([
        'cookie_httponly' => true,
        // Si es local y no hay HTTPS, relajamos secure para evitar perdida de sesión
        'cookie_secure'   => ($is_local && !$is_secure) ? false : true, 
        'cookie_samesite' => 'Lax',
    ]);
}

// 2. GENERACIÓN DE TOKEN CSRF (Si no existe)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. CARGA DE LIBRERÍAS CORE
require_once __DIR__ . '/../tovi/funciones.php';      
require_once __DIR__ . '/../seguridad/funciones.php';  

// 4. DETERMINAR ENTORNO
$script_actual = $_SERVER['PHP_SELF'];
$es_instalador = (strpos($script_actual, '/tovi/') !== false);

// 5. GESTIÓN DE LA INSTALACIÓN Y CONFIGURACIÓN
$path_config = __DIR__ . '/config.php';
$OPC = []; 

if (!file_exists($path_config)) {
    if (!$es_instalador) {
        header("Location: " . url_base() . "/tovi/pacheco.php");
        exit;
    }
} else {
    require_once $path_config;
    
    // Conexión a DB (Silenciamos errores con @ para manejarlos nosotros)
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
            $OPC = get_all_opciones(); 
            $estado = $OPC['estado'] ?? '';
            
            if ($estado === 'instalando' && !$es_instalador) {
                header("Location: " . url_base() . "/tovi/pacheco.php");
                exit;
            }
        }
    }
}

// 8. FUNCIONES AUXILIARES DE ESTADO (Checking)
if (!function_exists('checking')) {
    /**
     * Verifica si el usuario tiene una sesión válida
     */
    function checking() { 
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']); 
    }
}
?>