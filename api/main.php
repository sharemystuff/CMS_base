<?php
/* api/main.php */

/**
 * CMS BASE - ARCHIVO MAESTRO DE CARGA
 * Centraliza la configuración, seguridad y funciones core.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. GESTIÓN DE LA INSTALACIÓN (Pacheco)
// Detectamos si ya estamos en la carpeta tovi para evitar bucles de redirección
$es_instalador = (strpos($_SERVER['PHP_SELF'], '/tovi/') !== false);

if (!file_exists(__DIR__ . '/config.php')) {
    if (!$es_instalador) {
        header("Location: tovi/pacheco.php");
        exit;
    }
} else {
    // Si el archivo existe, cargamos la configuración
    require_once __DIR__ . '/config.php';
    
    // Conexión principal a la base de datos
    $conexion = @new mysqli($DB_DATOS['host'], $DB_DATOS['user'], $DB_DATOS['pass'], $DB_DATOS['name']);
    
    if ($conexion->connect_error) {
        if (!$es_instalador) {
            header("Location: tovi/pacheco.php?error=db_connection");
            exit;
        }
    } else {
        $conexion->set_charset("utf8mb4");
    }
}

// 2. CARGA DE CAPAS DE FUNCIONES
// Solo cargamos si los archivos existen para evitar Fatal Errors durante la instalación limpia
if (file_exists(__DIR__ . '/../tovi/funciones.php')) {
    require_once __DIR__ . '/../tovi/funciones.php';
}

if (file_exists(__DIR__ . '/funciones_model.php')) {
    require_once __DIR__ . '/funciones_model.php';
}

if (file_exists(__DIR__ . '/../seguridad/funciones.php')) {
    require_once __DIR__ . '/../seguridad/funciones.php';
}

// 3. INICIALIZACIÓN DE OPCIONES GLOBALES
$OPC = [];
if (isset($conexion) && !$conexion->connect_error) {
    // Solo intentamos leer opciones si la tabla existe
    $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
    if ($check_table && $check_table->num_rows > 0) {
        $OPC = get_all_opciones();
    }
}

// 4. LÓGICA DE SESIÓN Y AUTO-LOGIN
/**
 * Verifica si hay una sesión activa.
 */
function checking() {
    return isset($_SESSION['user_id']);
}

// Si no hay sesión, intentamos recuperar mediante cookie
if (!checking() && isset($conexion) && !$conexion->connect_error) {
    if (file_exists(__DIR__ . '/../seguridad/funciones.php')) {
        intentar_auto_login($conexion);
    }
}

// 5. CSRF PROTECTION (Generación de token si no existe)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Valida el token CSRF recibido
 */
function validarCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}