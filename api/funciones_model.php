<?php
/* api/funciones_model.php */

require __DIR__ . '/../tools/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

function generar_token_recuperacion($email) {
    global $conexion;
    $token = bin2hex(random_bytes(32));
    $expira = date("Y-m-d H:i:s", strtotime('+5 minutes'));
    $stmt = $conexion->prepare("UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE email = ? AND activo = 1");
    $stmt->bind_param("sss", $token, $expira, $email);
    return ($stmt->execute() && $conexion->affected_rows > 0) ? $token : false;
}

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

function actualizar_password_recuperada($token, $nueva_pass) {
    global $conexion;
    $pass_segura = password_hash($nueva_pass, PASSWORD_BCRYPT);
    $stmt = $conexion->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expira = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $pass_segura, $token);
    return $stmt->execute();
}

// ============================================================
// 4. MOTOR DE ENVÍO DE CORREOS (UNIFICADO)
// ============================================================

function enviar_email($destinatario, $asunto, $cuerpo_html) {
    // Usamos las funciones de obtención de opciones que ya existen
    $m_host = get_opcion('mailer_host');
    $m_user = get_opcion('mailer_username');
    $m_pass = get_opcion('mailer_password');
    $m_port = get_opcion('mailer_port');

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $m_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $m_user;
        $mail->Password   = $m_pass;
        $mail->Port       = $m_port;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPSecure = ($m_port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($m_user, 'Admin CMS BASE');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}