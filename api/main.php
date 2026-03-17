<?php
/* api/main.php */

// 1. INICIO DE SESIÓN SEGURO (HTTPS)
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => true, 
        'cookie_samesite' => 'Lax',
    ]);
}

// 2. CARGA DE LIBRERÍAS CORE
require_once __DIR__ . '/../tovi/funciones.php';      
require_once __DIR__ . '/../seguridad/funciones.php';  

// 3. DETERMINAR ENTORNO
$script_actual = $_SERVER['PHP_SELF'];
$es_instalador = (strpos($script_actual, '/tovi/') !== false);

// 4. GESTIÓN DE LA INSTALACIÓN Y CONFIGURACIÓN
$path_config = __DIR__ . '/config.php';
$OPC = []; 

if (!file_exists($path_config)) {
    if (!$es_instalador) {
        header("Location: " . url_base() . "/tovi/pacheco.php");
        exit;
    }
} else {
    require_once $path_config;
    
    // Conexión a DB
    $conexion = @new mysqli($DB_DATOS['host'], $DB_DATOS['user'], $DB_DATOS['pass'], $DB_DATOS['name']);
    
    if ($conexion->connect_error) {
        if (!$es_instalador) {
            die("Error de conexión a la base de datos.");
        }
    } else {
        $conexion->set_charset("utf8mb4");

        // 5. CARGA DEL MODELO
        if (file_exists(__DIR__ . '/funciones_model.php')) {
            include_once __DIR__ . '/funciones_model.php';
        }

        // 6. VALIDACIÓN DE ESTADO DEL SISTEMA
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

// 7. FUNCIONES AUXILIARES DE ESTADO
if (!function_exists('checking')) {
    function checking() { 
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']); 
    }
}

// 8. AUTO-LOGIN
if (isset($conexion) && !$conexion->connect_error) {
    if (!checking() && function_exists('intentar_auto_login')) {
        intentar_auto_login($conexion);
    }
}

// 9. GENERACIÓN DE TOKEN CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}