<?php
/* api/main.php */

/**
 * CMS BASE - ARCHIVO MAESTRO DE CARGA
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. DETERMINAR RUTAS Y ESTADO
$script_actual = $_SERVER['PHP_SELF'];
$es_instalador = (strpos($script_actual, '/tovi/') !== false);

// Determinamos la ruta base para redirecciones consistentes
// Si tu proyecto está en una subcarpeta (ej: /cms/), esto lo detectará
$base_path = rtrim(str_replace(basename($script_actual), '', $script_actual), '/');
// Ajuste para cuando estamos en subcarpetas profundas
if ($es_instalador) {
    $base_url = str_replace('/tovi', '', $base_path);
} else {
    $base_url = $base_path;
}

// 2. GESTIÓN DE LA INSTALACIÓN (Pacheco)
if (!file_exists(__DIR__ . '/config.php')) {
    if (!$es_instalador) {
        // Redirección absoluta relativa a la raíz del sitio
        header("Location: " . $base_url . "/tovi/pacheco.php");
        exit;
    }
} else {
    require_once __DIR__ . '/config.php';
    
    $conexion = @new mysqli($DB_DATOS['host'], $DB_DATOS['user'], $DB_DATOS['pass'], $DB_DATOS['name']);
    
    if ($conexion->connect_error) {
        if (!$es_instalador) {
            header("Location: " . $base_url . "/tovi/pacheco.php?error=db_connection");
            exit;
        }
    } else {
        $conexion->set_charset("utf8mb4");
    }
}

// 3. CARGA DE FUNCIONES (Solo si existen)
if (file_exists(__DIR__ . '/../tovi/funciones.php')) {
    include_once __DIR__ . '/../tovi/funciones.php';
}
if (file_exists(__DIR__ . '/funciones_model.php')) {
    include_once __DIR__ . '/funciones_model.php';
}
if (file_exists(__DIR__ . '/../seguridad/funciones.php')) {
    include_once __DIR__ . '/../seguridad/funciones.php';
}

// 4. LÓGICA DE OPCIONES
$OPC = [];
if (isset($conexion) && !$conexion->connect_error) {
    $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
    if ($check_table && $check_table->num_rows > 0) {
        $OPC = get_all_opciones();
    }
}

// 5. SESIÓN Y CSRF
function checking() {
    return isset($_SESSION['user_id']);
}

if (!checking() && isset($conexion) && !$conexion->connect_error) {
    if (function_exists('intentar_auto_login')) {
        intentar_auto_login($conexion);
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function validarCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}