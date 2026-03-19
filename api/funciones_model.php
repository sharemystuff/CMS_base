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


// ============================================================
// GESTIÓN DE DATOS ADICIONALES DE LOS USUARIOS
// ============================================================

function obtener_datos_usuario($id) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT id, nombre, nickname, email, rol, imagen, fecha, admin FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function actualizar_datos_usuario($id, $nombre, $nickname) {
    global $conexion;
    $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, nickname = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nombre, $nickname, $id);
    return $stmt->execute();
}

function actualizar_meta_usuario($usu_id, $usu_key, $usu_valor) {
    // Aprovechamos la función del núcleo que ya gestiona UPSERT (Insertar o Actualizar) de forma segura
    return crear_meta_usuario($usu_id, $usu_key, $usu_valor);
}

function borrar_meta_usuario($usu_id, $usu_key) {
    global $conexion;
    if (empty($usu_id) || empty($usu_key)) return false;

    $stmt = $conexion->prepare("DELETE FROM usuarios_meta WHERE usu_id = ? AND usu_key = ?");
    $stmt->bind_param("is", $usu_id, $usu_key);
    return $stmt->execute();
}

function procesar_avatar($usu_id, $base64_string) {
    global $conexion;
    
    // 1. Limpieza y validación base
    $data = explode(',', $base64_string);
    $contenido = base64_decode(end($data));
    
    if (!$contenido) return ['status' => false, 'msg' => 'Error al decodificar imagen'];

    // 2. Crear imagen desde string
    $imagen = @imagecreatefromstring($contenido);
    if (!$imagen) return ['status' => false, 'msg' => 'El archivo no es una imagen válida'];

    // 3. Preparar directorio
    $dir_relativo = 'subidas/perfiles/';
    $dir_absoluto = __DIR__ . '/../' . $dir_relativo;
    
    if (!file_exists($dir_absoluto)) {
        mkdir($dir_absoluto, 0755, true);
    }

    // 4. Guardar como JPG Calidad 80
    $nombre_archivo = 'avatar_' . $usu_id . '_' . time() . '.jpg';
    $ruta_final = $dir_absoluto . $nombre_archivo;
    
    imagejpeg($imagen, $ruta_final, 80);
    imagedestroy($imagen);

    // 5. Actualizar DB
    $url_db = $dir_relativo . $nombre_archivo;
    $stmt = $conexion->prepare("UPDATE usuarios SET imagen = ? WHERE id = ?");
    $stmt->bind_param("si", $url_db, $usu_id);
    $stmt->execute();

    return ['status' => true, 'url' => $url_db];
}

function cambiar_password_usuario($id, $actual, $nueva) {
    global $conexion;
    
    // 1. Obtener hash actual
    $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        // 2. Verificar contraseña actual
        if (password_verify($actual, $row['password'])) {
            // 3. Validar complejidad nueva (Básico: longitud)
            if (strlen($nueva) < 8) return ['status' => false, 'msg' => 'La contraseña nueva es muy corta (mínimo 8 caracteres).'];
            
            // 4. Actualizar con nuevo hash
            $nuevo_hash = password_hash($nueva, PASSWORD_BCRYPT);
            $stmt_up = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt_up->bind_param("si", $nuevo_hash, $id);
            
            if($stmt_up->execute()) return ['status' => true, 'msg' => 'Contraseña actualizada correctamente.'];
        } else {
            return ['status' => false, 'msg' => 'La contraseña actual no es correcta.'];
        }
    }
    return ['status' => false, 'msg' => 'Error de usuario.'];
}