<?php
/* seguridad/funciones.php */

/**
 * CAPA DE SALIDA: Escapa HTML para prevenir XSS.
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * CAPA DE ENTRADA: Limpieza de datos extrema.
 */
function limpiar_entrada($data, $es_password = false) {
    if (is_array($data)) {
        return array_map(function($item) use ($es_password) {
            return limpiar_entrada($item, $es_password);
        }, $data);
    }
    $data = trim($data);
    $data = str_replace(["\r", "\n"], '', $data);
    
    if ($es_password) return $data; // No tocamos la clave pura para el hash
    
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validación CSRF con comparación de tiempo constante.
 */
function validarCSRF($token_recibido) {
    if (!isset($_SESSION['csrf_token']) || empty($token_recibido)) return false;
    return hash_equals($_SESSION['csrf_token'], $token_recibido);
}

/**
 * Lógica de autenticación centralizada.
 */
function iniciar_sesion($email, $password) {
    global $conexion;
    
    $stmt = $conexion->prepare("SELECT id, nombre, password, rol, activo, admin, imagen FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        // Solo permitimos entrar si la cuenta está activa (confirmada)
        if ($usuario['activo'] == 1 && password_verify($password, $usuario['password'])) {
            
            session_regenerate_id(true); // Previene fijación de sesión
            
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nombre'] = $usuario['nombre'];
            $_SESSION['user_rol'] = $usuario['rol'];
            $_SESSION['user_modo'] = $usuario['admin']; // Guardamos preferencia en sesión
            $_SESSION['user_imagen'] = $usuario['imagen']; // Guardamos avatar en sesión
            
            return true;
        }
    }
    return false;
}

/**
 * Verifica si hay una sesión activa o un auto-login válido.
 */
function sesion_activa() {
    global $conexion;
    if (isset($_SESSION['user_id'])) return true;
    return intentar_auto_login($conexion);
}

/**
 * Intenta realizar un Auto-Login seguro mediante Cookie (Token Persistence)
 */
function intentar_auto_login($conexion) {
    if (isset($_COOKIE['session_token']) && !isset($_SESSION['user_id'])) {
        $token_recibido = $_COOKIE['session_token'];
        $token_hash = hash('sha256', $token_recibido);

        $stmt = $conexion->prepare("SELECT id, nombre, rol, admin, imagen FROM usuarios WHERE session_token = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("s", $token_hash);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['user_modo'] = $user['admin'];
            $_SESSION['user_imagen'] = $user['imagen'];
            return true;
        }
    }
    return false;
}

/**
 * BLOQUEO DE ACCESO POR ROLES
 */
function restringir_acceso($roles_permitidos) {
    if (!sesion_activa()) {
        header("Location: " . url_base() . "/public/login.php?error=sesion_expirada");
        exit;
    }
    if (!in_array($_SESSION['user_rol'], $roles_permitidos)) {
        header("Location: " . url_base() . "/public/login.php?error=permiso_denegado");
        exit;
    }
}

/**
 * Genera una contraseña aleatoria segura.
 */
function contrasenia_aleatoria($longitud = 16) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%&*+';
    $password = '';
    $max = strlen($caracteres) - 1;
    for ($i = 0; $i < $longitud; $i++) {
        $password .= $caracteres[random_int(0, $max)];
    }
    return $password;
}