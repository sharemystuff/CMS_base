<?php
/* api/main.php */
date_default_timezone_set('America/Santiago');

if (!defined('AUTH_SALT')) {
    define('AUTH_SALT', 'CMS_BASE_PROTECTION_2026_PELIN');
}

// 1. Detección de entorno seguro (HTTPS)
$is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

// 2. Configuración de Cookies de Sesión
ini_set('session.cookie_httponly', 1); // Bloquea acceso de JS a la sesión
ini_set('session.use_only_cookies', 1); // Evita sesión por URL

if ($is_secure) {
    ini_set('session.cookie_secure', 1);   // Solo viaja por HTTPS
    ini_set('session.cookie_samesite', 'Strict'); // Máxima protección contra CSRF externo
} else {
    ini_set('session.cookie_samesite', 'Lax');
}

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../tovi/funciones.php';
require_once __DIR__ . '/../seguridad/funciones.php';

global $OPC;
$OPC = get_all_opciones();

// Generación de Token CSRF único para la sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verifica sesión activa o persistencia por cookie (Validación por Hash)
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
            session_regenerate_id(true); // Previene fijación de sesión
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
 * Valida el Token CSRF y el origen de la petición
 */
function validarCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }

    if (isset($_SERVER['HTTP_REFERER'])) {
        $host_peticion = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        $mi_host = $_SERVER['SERVER_NAME'];
        if ($host_peticion !== $mi_host) {
            return false;
        }
    }

    return true;
}

// 3. Cabeceras de Seguridad Globales
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");