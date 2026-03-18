<?php
/* seguridad/funciones.php */

/**
 * CAPA DE SALIDA: Escapa HTML para prevenir XSS.
 */
function e($cadena) {
    return htmlspecialchars($cadena ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * CAPA DE ENTRADA: Limpieza de datos.
 */
function limpiar_entrada($datos, $es_password = false) {
    if (is_array($datos)) {
        return array_map(function($item) use ($es_password) {
            return limpiar_entrada($item, $es_password);
        }, $datos);
    }
    $datos = trim($datos);
    $datos = str_replace(["\r", "\n"], '', $datos);
    if ($es_password) return $datos; 
    return htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
}

function validarCSRF($token_recibido) {
    if (!isset($_SESSION['csrf_token']) || empty($token_recibido)) return false;
    return hash_equals($_SESSION['csrf_token'], $token_recibido);
}

/**
 * Verifica si hay una sesión válida. (Antiguo checking)
 */
function sesion_activa() {
    global $conexion;
    if (isset($_SESSION['user_id'])) return true;
    return intentar_autologin($conexion);
}

/**
 * Lógica de autenticación. (Antiguo login)
 */
function iniciar_sesion($email, $password) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT id, nombre, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if ($usuario['activo'] == 1 && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_rol'] = $usuario['rol'];
            return true;
        }
    }
    return false;
}

function intentar_autologin($conexion) {
    if (isset($_COOKIE['session_token']) && !isset($_SESSION['user_id'])) {
        $token_hash = hash('sha256', $_COOKIE['session_token']);
        $stmt = $conexion->prepare("SELECT id, nombre, rol FROM usuarios WHERE session_token = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            return true;
        }
    }
    return false;
}

function restringir_acceso($roles_permitidos) {
    if (!sesion_activa() || !in_array($_SESSION['user_rol'], $roles_permitidos)) {
        header("Location: " . url_base() . "/public/login.php?error=restringido");
        exit;
    }
}