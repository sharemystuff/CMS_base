<?php
/* api/funciones_model.php */

/**
 * CMS BASE - MODELO DE DATOS DE LA API
 * Aquí se gestionan todas las consultas directas a la base de datos.
 */

// ============================================================
// 1. GESTIÓN DE OPCIONES DEL SISTEMA
// ============================================================

/**
 * Obtiene todas las opciones de la tabla 'opciones' para configurar el CMS.
 */
function get_all_opciones() {
    global $conexion;
    $opciones = [];
    $resultado = $conexion->query("SELECT nombre, valor FROM opciones");
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $opciones[$fila['nombre']] = $fila['valor'];
        }
    }
    return $opciones;
}

// ============================================================
// 2. LÓGICA DE INTENTOS DE LOGIN (Login Throttling)
// ============================================================

/**
 * Registra un intento fallido en la base de datos
 */
function registrar_intento_fallido($ip, $email) {
    global $conexion;
    $stmt = $conexion->prepare("INSERT INTO login_intentos (ip, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $ip, $email);
    return $stmt->execute();
}

/**
 * Cuenta cuántos fallos tiene una IP en los últimos 5 minutos.
 */
function contar_intentos_fallidos($ip) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM login_intentos WHERE ip = ? AND fecha > (NOW() - INTERVAL 5 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)$res['total'];
}

/**
 * Limpia los intentos tras un login exitoso.
 */
function limpiar_intentos_ip($ip) {
    global $conexion;
    $stmt = $conexion->prepare("DELETE FROM login_intentos WHERE ip = ?");
    $stmt->bind_param("s", $ip);
    return $stmt->execute();
}

/**
 * Mantenimiento: Borra registros de más de 24 horas.
 */
function purgar_intentos_viejos() {
    global $conexion;
    return $conexion->query("DELETE FROM login_intentos WHERE fecha < (NOW() - INTERVAL 1 DAY)");
}

// ============================================================
// 3. LÓGICA DE RECUPERACIÓN DE CONTRASEÑA (Password Reset)
// ============================================================

/**
 * Genera un token único para recuperación y lo guarda en la DB (Expiración 1h).
 */
function generar_token_recuperacion($email) {
    global $conexion;
    
    // Solo generamos token para usuarios activos
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($user = $res->fetch_assoc()) {
        $token = bin2hex(random_bytes(32)); // Token criptográfico de 64 caracteres
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));
        
        $update = $conexion->prepare("UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE id = ?");
        $update->bind_param("ssi", $token, $expira, $user['id']);
        
        if ($update->execute()) {
            return $token;
        }
    }
    return false; // Seguridad: Si no existe, el controlador manejará la respuesta genérica
}

/**
 * Valida si un token es real y no ha expirado todavía.
 */
function validar_token_reset($token) {
    global $conexion;
    if (empty($token)) return false;

    // Buscamos usuario donde el token coincida y la fecha sea mayor a la actual
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expira > NOW() LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    
    return $res->fetch_assoc(); // Retorna el array del usuario o null si falló
}

/**
 * Actualiza la contraseña y anula los tokens de forma atómica.
 */
function resetear_password($user_id, $nuevo_pass) {
    global $conexion;
    // Usamos PASSWORD_BCRYPT por el estándar definido en el contexto
    $hash = password_hash($nuevo_pass, PASSWORD_BCRYPT);
    
    // Al actualizar, limpiamos el token para que no pueda ser reutilizado
    $stmt = $conexion->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?");
    $stmt->bind_param("si", $hash, $user_id);
    return $stmt->execute();
}