<?php
/* api/main.php */
date_default_timezone_set('America/Santiago');

// 1. Configuración de Sesión Segura
if (session_status() === PHP_SESSION_NONE) {
    $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', $is_secure ? 'Strict' : 'Lax');
    if ($is_secure) ini_set('session.cookie_secure', 1);

    session_start();
}

// 2. Escudo contra Revelación de Rutas (Error Handling)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../seguridad/php_errors.log');

// 3. Cabeceras de Seguridad Globales
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// 4. Carga de Dependencias
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../tovi/funciones.php';
require_once __DIR__ . '/../seguridad/funciones.php';

// 5. Carga de Opciones Globales
global $OPC;
$OPC = get_all_opciones();

// 6. Token CSRF Automático
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verifica sesión activa o persistencia por cookie (Actualizado a BCRYPT)
 */
function checking() {
    global $conexion;

    if (isset($_SESSION['user_id'])) {
        return true;
    }

    if (isset($_COOKIE['session_token'])) {
        $token_recibido = $_COOKIE['session_token'];
        $token_hash_recibido = hash('sha256', $token_recibido);
        
        $stmt = $conexion->prepare("SELECT id, nombre, nickname, rol, activo FROM usuarios WHERE session_token = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $token_hash_recibido);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];
            return true;
        } else {
            setcookie('session_token', '', time() - 3600, '/');
        }
    }
    return false;
}

/**
 * Valida el Token CSRF y el origen (Referer)
 */
function validarCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    if (isset($_SERVER['HTTP_REFERER'])) {
        $host_peticion = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        if ($host_peticion !== $_SERVER['SERVER_NAME']) return false;
    }
    return true;
}