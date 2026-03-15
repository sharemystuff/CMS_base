<?php
/**
 * CMS BASE - SEGURIDAD
 * Este archivo contiene las funciones encargadas de blindar el sistema.
 */

/**
 * Encripta contraseñas usando el algoritmo BCRYPT.
 * Es el estándar actual (2026) y mucho más seguro que MD5 o SHA puro.
 * @param string $password Contraseña en texto plano.
 * @return string|false El hash de la contraseña o false si falla.
 */
function encode_pass($password) {
    if (!$password) return false;
    // password_hash genera una cadena de 60 caracteres que incluye la "sal" automáticamente.
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Compara una contraseña enviada por formulario con el hash de la base de datos.
 * @param string $password Texto plano ingresado por el usuario.
 * @param string $hash El hash almacenado en la DB.
 * @return bool True si coinciden, False si no.
 */
function verificar_pass($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Genera una contraseña aleatoria y robusta. [cite: 32]
 * Ideal para sugerencias en el registro o reseteo de claves. [cite: 102]
 * @return string Password de 16 caracteres. [cite: 33]
 */
function random_pass() {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    // Mezclamos la cadena y cortamos los primeros 16.
    return substr(str_shuffle($caracteres), 0, 16);
}

/**
 * Sanitiza y valida un correo electrónico. [cite: 37]
 * @param string $email El correo a evaluar.
 * @return bool True si es válido, False si no. [cite: 38]
 */
function email_valido($email) {
    // Eliminamos caracteres ilegales del correo.
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    // Validamos que tenga formato real de email.
    return (filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
}

/**
 * Crea un Token CSRF para proteger formularios POST. [cite: 76]
 * Evita que atacantes envíen formularios desde otros sitios.
 * @return string El token generado.
 */
function generar_csrf() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        // Generamos 32 bytes de aleatoriedad criptográfica.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Limpia cualquier string para prevenir ataques XSS. [cite: 77]
 * Convierte caracteres especiales en entidades HTML seguras.
 * @param string $dato Texto a limpiar.
 * @return string Texto sanitizado.
 */
function limpiar_entrada($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}