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
 * @param bool $es_password Si es true, no escapa HTML para no romper el hash de la clave.
 */
function limpiar_entrada($data, $es_password = false) {
    if (is_array($data)) {
        return array_map(function($item) use ($es_password) {
            return limpiar_entrada($item, $es_password);
        }, $data);
    }
    $data = trim($data);
    $data = str_replace(["\r", "\n"], '', $data);
    
    if ($es_password) return $data; // Seguridad Extrema: No tocar la clave pura
    
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

    // Buscamos al usuario por email
    $stmt = $conexion->prepare("SELECT id, nombre, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($u = $resultado->fetch_assoc()) {
        if ($u['activo'] != 1) return false;

        // VERIFICACIÓN CON BCRYPT (Soluciona la inconsistencia con encode_pass)
        if (password_verify($pass_plano, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_nombre'] = $u['nombre'];
            $_SESSION['user_rol'] = $u['rol'];
            return true;
        }
    }
    
    sleep(2); // Anti-Fuerza Bruta
    return false;
}

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

        $stmt = $conexion->prepare("SELECT id, nombre, nickname, rol FROM usuarios WHERE session_token = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            return true;
        }
    }
    return false;
}

/**
 * BLOQUEO DE ACCESO RESTRINGIDO
 */
function restringir_acceso($roles_permitidos = []) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../public/login.php?error=sesion_expirada");
        exit;
    }
    if (!empty($roles_permitidos) && !in_array($_SESSION['user_rol'], $roles_permitidos)) {
        header("Location: ../admin/admin.php?error=sin_permisos");
        exit;
    }
}
?>