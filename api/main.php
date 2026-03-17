<?php
/* api/main.php */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. DETERMINAR RUTAS Y ESTADO
$script_actual = $_SERVER['PHP_SELF'];
$es_instalador = (strpos($script_actual, '/tovi/') !== false);

// 2. GESTIÓN DE LA INSTALACIÓN
$path_config = __DIR__ . '/config.php';

if (!file_exists($path_config)) {
    if (!$es_instalador) {
        // Redirección absoluta desde la raíz para evitar rutas mañosas
        header("Location: /tovi/pacheco.php");
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

        // CARGA DE FUNCIONES (Surgido de la auditoría: las cargamos aquí para que estén disponibles)
        if (file_exists(__DIR__ . '/../tovi/funciones.php')) include_once __DIR__ . '/../tovi/funciones.php';
        if (file_exists(__DIR__ . '/funciones_model.php')) include_once __DIR__ . '/funciones_model.php';
        if (file_exists(__DIR__ . '/../seguridad/funciones.php')) include_once __DIR__ . '/../seguridad/funciones.php';

        // 3. VALIDACIÓN DE ESTADO (El Corazón del Sistema)
        $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
        if ($check_table && $check_table->num_rows > 0) {
            $estado = get_opcion('estado');
            
            // Si el sitio está en fase de instalación y el usuario intenta navegar fuera
            if ($estado === 'instalando' && !$es_instalador) {
                header("Location: /tovi/pacheco.php");
                exit;
            }
        }
    }
}

// 4. LÓGICA DE OPCIONES Y SESIÓN
$OPC = [];
function checking() { return isset($_SESSION['user_id']); }

if (isset($conexion) && !$conexion->connect_error) {
    // Si la tabla opciones existe, cargamos todo
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