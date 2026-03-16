<?php
/* seguridad/funciones.php */

/**
 * CAPA DE SALIDA: Escapa HTML para prevenir XSS.
 * Úsala en tus archivos de /admin/ o /public/ al hacer echo.
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * CAPA DE ENTRADA: Limpieza de datos.
 */
function limpiar_entrada($data) {
    if (is_null($data)) return '';
    // Mantenemos htmlspecialchars aquí por compatibilidad con tus vistas actuales
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Validación de email
function email_valido($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Hash de contraseña (SHA512 + SALT)
 */
function encode_pass($password, $manual_salt = null) {
    $salt = $manual_salt ?? get_opcion('salt_key');
    if (!$salt) { $salt = 'fallback_salt_cms_base'; } 
    return hash('sha512', $password . $salt);
}

/**
 * Verificación de contraseña compatible
 */
function verificar_pass($pass, $hash_en_db) {
    $hash_peticion = encode_pass($pass);
    return hash_equals($hash_peticion, $hash_en_db);
}

/**
 * Intenta realizar un Auto-Login seguro
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
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];
            return true;
        }
    }
    return false;
}