<?php
/* api/main.php */
date_default_timezone_set('America/Santiago');

if (!defined('AUTH_SALT')) {
    define('AUTH_SALT', 'CMS_BASE_PROTECTION_2026_PELIN');
}

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../tovi/funciones.php';
require_once __DIR__ . '/../seguridad/funciones.php';

// CARGA MASIVA DE OPCIONES (Optimización solicitada)
// Ahora todas las opciones están disponibles en el array global $OPC
global $OPC;
$OPC = get_all_opciones();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verifica sesión activa o persistencia por cookie
 */
function checking() {
    global $conexion;

    if (isset($_SESSION['user_id'])) {
        return true;
    }

    if (isset($_COOKIE['session_token'])) {
        $token = $_COOKIE['session_token'];
        
        $stmt = $conexion->prepare("SELECT id, nombre, nickname, rol, activo FROM usuarios WHERE session_token = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $token);
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

function validarCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");