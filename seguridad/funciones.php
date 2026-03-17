<?php
/* seguridad/funciones.php */

/**
 * CAPA DE SALIDA: Escapa HTML para prevenir XSS.
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * CAPA DE ENTRADA: Limpieza de datos.
 */
function limpiar_entrada($data) {
    if (is_array($data)) {
        return array_map('limpiar_entrada', $data);
    }
    $data = trim($data);
    $data = str_replace(["\r", "\n"], '', $data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica el token CSRF con comparación de tiempo constante.
 */
function validarCSRF($token_recibido) {
    if (!isset($_SESSION['csrf_token']) || empty($token_recibido)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token_recibido);
}

/**
 * Lógica de autenticación centralizada por EMAIL.
 * Ajustada a la estructura: id, nombre, nickname, email, password, rol, activo.
 */
function login($email, $pass_plano) {
    global $conexion;
    if (!$conexion) return false;

    // Ajustado: La columna es 'password'
    $stmt = $conexion->prepare("SELECT id, nombre, nickname, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($user = $resultado->fetch_assoc()) {
        // Verificamos si la cuenta está activa (1) y la clave coincide
        if ($user['activo'] == 1 && password_verify($pass_plano, $user['password'])) {
            
            // Seguridad: Regenerar ID tras login exitoso
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];

            return true;
        }
    }
    return false;
}

/**
 * Obtiene la IP real del cliente.
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'];
}

function email_valido($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Intenta realizar un Auto-Login seguro mediante Cookie
 */
function intentar_auto_login($conexion) {
    if (isset($_COOKIE['session_token']) && !isset($_SESSION['user_id'])) {
        $token_recibido = $_COOKIE['session_token'];
        $token_hash = hash('sha256', $token_recibido);

        // Ajustado a tus columnas: session_token, activo, etc.
        $stmt = $conexion->prepare("SELECT id, nombre, nickname, rol FROM usuarios WHERE session_token = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];
            return true;
        }
    }
    return false;
}

/**
 * BLOQUEO DE ACCESO RESTRINGIDO
 */
function restringir_acceso($roles_permitidos = ['admin', 'owner']) {
    if (!checking()) {
        header('Location: ../public/login.php');
        exit;
    }
    if (!in_array($_SESSION['user_rol'], $roles_permitidos)) {
        header('Location: admin.php?error=no_permission');
        exit;
    }
}