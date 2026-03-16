<?php
/* api/main.php */

// 1. Zona Horaria
date_default_timezone_set('America/Santiago');

// 2. Blindaje de Cookies de Sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();
include_once __DIR__ . '/db.php';
include_once __DIR__ . '/../tovi/funciones.php';
include_once __DIR__ . '/../seguridad/funciones.php';

/**
 * Verifica si el usuario está logueado o tiene cookie de sesión
 */
function checking() {
    global $conexion;
    
    if (isset($_SESSION['user_id'])) {
        return true; 
    }
    
    if (isset($_COOKIE['session_token'])) {
        $token = limpiar_entrada($_COOKIE['session_token']);
        
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
        }
    }
    return false;
}

/**
 * Limpia entradas para evitar XSS
 */
// function limpiar_entrada($data) {
//     $data = trim($data);
//     $data = stripslashes($data);
//     $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
//     return $data;
// }