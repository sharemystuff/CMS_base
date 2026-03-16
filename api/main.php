<?php
/* api/main.php */
date_default_timezone_set('America/Santiago');

if (!defined('AUTH_SALT')) {
    define('AUTH_SALT', 'CMS_BASE_PROTECTION_2026_PELIN');
}

// 1. Detección automática de entorno seguro
$is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

// 2. Configuración de Sesiones y Cookies dinámica
ini_set('session.cookie_httponly', 1); // Impide acceso vía JavaScript
ini_set('session.use_only_cookies', 1); // Evita ataques de fijación de sesión por URL

if ($is_secure) {
    // Si hay HTTPS (Desarrollo con mkcert o Producción real)
    ini_set('session.cookie_secure', 1);   // Solo envía la cookie por canales cifrados
    ini_set('session.cookie_samesite', 'Strict'); // Máxima protección contra CSRF
} else {
    // Solo para casos de emergencia en HTTP plano
    ini_set('session.cookie_samesite', 'Lax');
}

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../tovi/funciones.php';
require_once __DIR__ . '/../seguridad/funciones.php';

global $OPC;
$OPC = get_all_opciones();

// Generación de Token CSRF si no existe
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
            session_regenerate_id(true); // Previene Session Fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];
            return true;
        } else {
            // Limpieza de cookie inválida
            setcookie('session_token', '', time() - 3600, '/');
        }
    }

    return false;
}

/**
 * Valida el Token CSRF y el origen de la petición
 */
function validarCSRF($token) {
    // 1. Validar coincidencia de Token (Time-attack safe)
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }

    // 2. Validar que la petición venga de nuestro dominio (Referer check dinámico)
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
header("X-Frame-Options: DENY"); // Previene Clickjacking
header("X-Content-Type-Options: nosniff"); // Previene MIME-sniffing
header("Referrer-Policy: strict-origin-when-cross-origin");