<?php
/* api/main.php */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. DETERMINAR RUTAS Y ESTADO
$script_actual = $_SERVER['PHP_SELF'];
$es_instalador = (strpos($script_actual, '/tovi/') !== false);

// CARGA PREVENTIVA DE FUNCIONES (Para que url_base y seguridad siempre existan)
if (file_exists(__DIR__ . '/../tovi/funciones.php')) include_once __DIR__ . '/../tovi/funciones.php';
if (file_exists(__DIR__ . '/../seguridad/funciones.php')) include_once __DIR__ . '/../seguridad/funciones.php';

// 2. GESTIÓN DE LA INSTALACIÓN
$path_config = __DIR__ . '/config.php';

if (!file_exists($path_config)) {
    if (!$es_instalador) {
        // Redirección usando url_base para evitar errores de carpeta
        header("Location: " . url_base() . "/tovi/pacheco.php");
        exit;
    }
} else {
    require_once $path_config;
    
    // Intentamos conectar
    $conexion = @new mysqli($DB_DATOS['host'], $DB_DATOS['user'], $DB_DATOS['pass'], $DB_DATOS['name']);
    
    if ($conexion->connect_error) {
        if (!$es_instalador) {
            die("Error de conexión a la base de datos. Verifica api/config.php");
        }
    } else {
        $conexion->set_charset("utf8mb4");

        // Modelos adicionales
        if (file_exists(__DIR__ . '/funciones_model.php')) include_once __DIR__ . '/funciones_model.php';

        // 3. VALIDACIÓN DE ESTADO (El Corazón del Sistema)
        $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
        if ($check_table && $check_table->num_rows > 0) {
            // Cargamos opciones antes de chequear el estado
            $OPC = get_all_opciones();
            $estado = $OPC['estado'] ?? '';
            
            // Si el sitio está en fase de instalación y el usuario intenta navegar fuera
            if ($estado === 'instalando' && !$es_instalador) {
                header("Location: " . url_base() . "/tovi/pacheco.php");
                exit;
            }
        }
    }
}

// 4. LÓGICA DE OPCIONES Y SESIÓN
if (!isset($OPC)) { $OPC = []; }
function checking() { return isset($_SESSION['user_id']); }

if (isset($conexion) && !$conexion->connect_error) {
    // Si no se cargaron antes, se cargan aquí
    if (empty($OPC)) {
        $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
        if ($check_table && $check_table->num_rows > 0) {
            $OPC = get_all_opciones();
        }
    }
    
    if (!checking() && function_exists('intentar_auto_login')) {
        intentar_auto_login($conexion);
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}