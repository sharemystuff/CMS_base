<?php
/* api/main.php */
date_default_timezone_set('America/Santiago');

if (!defined('AUTH_SALT')) {
    define('AUTH_SALT', 'c4ca4238a0b923820dcc509a6f75849b_CMS_BASE_2026');
}

// Blindaje de Cookies de Sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax'); // Protección adicional contra CSRF
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

// --- PUNTO 8.3: Generación de Token CSRF ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- PUNTO 8.4: Headers de Seguridad ---
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");

include_once __DIR__ . '/db.php';

// Carga de funciones con verificación de existencia
if (file_exists(__DIR__ . '/../tovi/funciones.php')) include_once __DIR__ . '/../tovi/funciones.php';
if (file_exists(__DIR__ . '/../seguridad/funciones.php')) include_once __DIR__ . '/../seguridad/funciones.php';

/**
 * PUNTO 8.2: Función de verificación con Prepared Statements
 */
function checking() {
    global $conexion;
    if (isset($_SESSION['user_id'])) {
        return true; 
    }
    if (isset($_COOKIE['session_token']) && !empty($_COOKIE['session_token'])) {
        $token = $_COOKIE['session_token'];
        // Uso estricto de Prepared Statements
        $stmt = $conexion->prepare("SELECT id, nombre, rol, activo FROM usuarios WHERE session_token = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($u = $res->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_nombre'] = $u['nombre'];
            $_SESSION['user_rol'] = $u['rol'];
            return true;
        } else {
            setcookie('session_token', '', time() - 42000, '/');
        }
    }
    return false;
}

/**
 * Función para validar el token CSRF en los POST
 */
function validarCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>