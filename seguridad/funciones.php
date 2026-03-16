<?php
/* seguridad/funciones.php */

/**
 * CMS BASE - Funciones de Seguridad
 */

// Limpieza de datos
function limpiar_entrada($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Validación de email
function email_valido($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Hash de contraseña (BCRYPT)
function encode_pass($pass) {
    return password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Verificación de contraseña
function verificar_pass($pass, $hash) {
    return password_verify($pass, $hash);
}

/**
 * Intenta realizar un Auto-Login mediante la cookie de persistencia
 */
function intentar_auto_login($conexion) {
    if (isset($_COOKIE['pelin_remember']) && !isset($_SESSION['user_id'])) {
        $token = $_COOKIE['pelin_remember'];

        // Buscamos al usuario que tenga este token
        $stmt = $conexion->prepare("SELECT id, nombre, rol FROM usuarios WHERE session_token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            // ¡Token válido! Recreamos la sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            return true;
        }
    }
    return false;
}