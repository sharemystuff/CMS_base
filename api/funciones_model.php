<?php
/* api/funciones_model.php */

/**
 * CMS BASE - MODELO DE DATOS DE LA API
 * Aquí se gestionan todas las consultas directas a la base de datos.
 */

// --- LÓGICA DE INTENTOS DE LOGIN (Login Throttling) ---

/**
 * Registra un intento fallido en la base de datos
 * @param string $ip IP del atacante/usuario
 * @param string $email Email que intentaron usar
 */
function registrar_intento_fallido($ip, $email) {
    global $conexion;
    $stmt = $conexion->prepare("INSERT INTO login_intentos (ip, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $ip, $email);
    return $stmt->execute();
}

/**
 * Cuenta cuántos fallos tiene una IP en la ventana de tiempo (5 min)
 * @param string $ip
 * @return int Total de intentos registrados
 */
function contar_intentos_fallidos($ip) {
    global $conexion;
    // Buscamos fallos en los últimos 5 minutos usando NOW() de SQL
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM login_intentos WHERE ip = ? AND fecha > (NOW() - INTERVAL 5 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    return (int)$resultado['total'];
}

/**
 * Limpia todos los intentos de una IP tras un login exitoso
 * @param string $ip
 */
function limpiar_intentos_ip($ip) {
    global $conexion;
    $stmt = $conexion->prepare("DELETE FROM login_intentos WHERE ip = ?");
    $stmt->bind_param("s", $ip);
    return $stmt->execute();
}

/**
 * Limpieza automática de registros viejos (Mantenimiento)
 * Borra intentos de más de 24 horas para no saturar la tabla login_intentos
 */
function purgar_intentos_viejos() {
    global $conexion;
    return $conexion->query("DELETE FROM login_intentos WHERE fecha < (NOW() - INTERVAL 1 DAY)");
}

// --- FUTURAS FUNCIONES DE MODELO ---
// Aquí podrías añadir funciones como:
// get_post_by_url($url)
// get_user_meta($user_id, $key)
// etc.