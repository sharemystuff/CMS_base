<?php
/* api/main.php */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. DETERMINAR RUTAS Y ESTADO
$script_actual = $_SERVER['PHP_SELF'];
// Detectamos si estamos en la carpeta tovi buscando la palabra en la ruta
$es_instalador = (strpos($script_actual, '/tovi/') !== false);

// 2. GESTIÓN DE LA INSTALACIÓN
$path_config = __DIR__ . '/config.php';

if (!file_exists($path_config)) {
    if (!$es_instalador) {
        // Si no estamos en el instalador y no hay config, vamos a pacheco
        // Usamos una ruta relativa simple que funciona desde la raíz
        header("Location: tovi/pacheco.php");
        exit;
    }
} else {
    // Si existe la configuración, la cargamos
    require_once $path_config;
    
    // Intentamos conectar
    $conexion = @new mysqli($DB_DATOS['host'], $DB_DATOS['user'], $DB_DATOS['pass'], $DB_DATOS['name']);
    
    if ($conexion->connect_error) {
        // Si falla la conexión y no estamos en el instalador, avisamos
        if (!$es_instalador) {
            die("Error de conexión a la base de datos. Verifica api/config.php");
        }
    } else {
        $conexion->set_charset("utf8mb4");
    }
}

// 3. CARGA DE FUNCIONES (Solo si no hay conflicto y los archivos existen)
if (file_exists(__DIR__ . '/../tovi/funciones.php')) {
    include_once __DIR__ . '/../tovi/funciones.php';
}
if (file_exists(__DIR__ . '/funciones_model.php')) {
    include_once __DIR__ . '/funciones_model.php';
}
if (file_exists(__DIR__ . '/../seguridad/funciones.php')) {
    include_once __DIR__ . '/../seguridad/funciones.php';
}

// 4. LÓGICA DE OPCIONES Y SESIÓN
$OPC = [];
function checking() { return isset($_SESSION['user_id']); }

if (isset($conexion) && !$conexion->connect_error) {
    $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
    if ($check_table && $check_table->num_rows > 0) {
        $OPC = get_all_opciones();
    }
    
    if (!checking() && function_exists('intentar_auto_login')) {
        intentar_auto_login($conexion);
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}