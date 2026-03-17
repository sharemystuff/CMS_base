<?php
/* api/funciones_model.php */

/**
 * CMS BASE - MODELO DE DATOS DE LA API
 */

// ============================================================
// 1. GESTIÓN DE OPCIONES DEL SISTEMA
// ============================================================

if (!function_exists('get_all_opciones')) {
    function get_all_opciones() {
        global $conexion;
        $opciones = [];
        // Ajustado a tus columnas: opcion_key, opcion_dato
        $resultado = $conexion->query("SELECT opcion_key, opcion_dato FROM opciones WHERE opcion_key NOT IN ('salt_key')");
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                $opciones[$row['opcion_key']] = $row['opcion_dato'];
            }
        }
        return $opciones;
    }
}

// ============================================================
// 2. LÓGICA DE INTENTOS DE LOGIN (Login Throttling)
// ============================================================

function registrar_intento_fallido($ip, $email) {
    global $conexion;
    $stmt = $conexion->prepare("INSERT INTO login_intentos (ip, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $ip, $email);
    return $stmt->execute();
}

function contar_intentos_fallidos($ip) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM login_intentos WHERE ip = ? AND fecha > (NOW() - INTERVAL 5 MINUTE)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)$res['total'];
}

function limpiar_intentos_ip($ip) {
    global $conexion;
    $stmt = $conexion->prepare("DELETE FROM login_intentos WHERE ip = ?");
    $stmt->bind_param("s", $ip);
    return $stmt->execute();
}

function purgar_intentos_viejos() {
    global $conexion;
    return $conexion->query("DELETE FROM login_intentos WHERE fecha < (NOW() - INTERVAL 1 DAY)");
}

// ============================================================
// 3. LÓGICA DE RECUPERACIÓN DE CONTRASEÑA
// ============================================================

/**
 * Genera token y actualiza la DB.
 */
function generar_token_recuperacion($email) {
    global $conexion;
    $token = bin2hex(random_bytes(32));
    $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));
    
    // Verificamos que sea un usuario activo antes de marcar el token
    $stmt = $conexion->prepare("UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE email = ? AND activo = 1");
    $stmt->bind_param("sss", $token, $expira, $email);
    
    return ($stmt->execute() && $conexion->affected_rows > 0) ? $token : false;
}

/**
 * Valida token y tiempo. Retorna datos del usuario.
 */
function validar_token_recuperacion($token) {
    global $conexion;
    if (empty($token)) return false;
    $ahora = date("Y-m-d H:i:s");
    $stmt = $conexion->prepare("SELECT id, email FROM usuarios WHERE reset_token = ? AND reset_expira > ? LIMIT 1");
    $stmt->bind_param("ss", $token, $ahora);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

/**
 * Proceso final de cambio de clave.
 */
function actualizar_password_recuperada($token, $nueva_pass) {
    global $conexion;
    $pass_segura = password_hash($nueva_pass, PASSWORD_BCRYPT);
    $stmt = $conexion->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expira = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $pass_segura, $token);
    return $stmt->execute();
}