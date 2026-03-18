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

function obtener_todas_las_opciones() {
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

// ============================================================
// 2. LÓGICA DE USUARIOS Y SEGURIDAD
// ============================================================

function actualizar_token_reset($email, $token) {
    global $conexion;
    $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));
    $stmt = $conexion->prepare("UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expira, $email);
    return $stmt->execute();
}

function validar_token_reset($token) {
    global $conexion;
    $ahora = date("Y-m-d H:i:s");
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expira > ? LIMIT 1");
    $stmt->bind_param("ss", $token, $ahora);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function cambiar_password_por_token($token, $nueva_pass) {
    global $conexion;
    $pass_segura = password_hash($nueva_pass, PASSWORD_BCRYPT);
    $stmt = $conexion->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expira = NULL WHERE reset_token = ?");
    $stmt->bind_param("ss", $pass_segura, $token);
    return $stmt->execute();
}

// ============================================================
// 3. MOTOR DE ENVÍO DE CORREOS (UNIFICADO)
// ============================================================

function mandar_correo($destinatario, $asunto, $cuerpo_html) {
    // Usamos las funciones de obtención de opciones traducidas
    $m_host = leer_opcion('mailer_host');
    $m_user = leer_opcion('mailer_username');
    $m_pass = leer_opcion('mailer_password');
    $m_port = leer_opcion('mailer_port');

    if (empty($m_host) || empty($m_user)) return false;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $m_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $m_user;
        $mail->Password   = $m_pass;
        $mail->Port       = $m_port;
        $mail->CharSet    = 'UTF-8';
        
        // Ajuste automático de seguridad según el puerto
        if ($m_port == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($m_port == 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($m_user, 'CMS BASE');
        $mail->addAddress($destinatario);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html;

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error de PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}