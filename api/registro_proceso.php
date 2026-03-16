<?php
/* api/registro_proceso.php */
include_once __DIR__ . '/main.php';

require __DIR__ . '/../tools/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$resp = ["status" => "error", "message" => "Solicitud no autorizada"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // VALIDACIÓN CSRF: Bloquea ataques de dominios externos (como lab.mahg)
    $token_recibido = $_POST['csrf_token'] ?? '';
    if (!validarCSRF($token_recibido)) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Token de seguridad inválido o expirado"]);
        exit;
    }

    $nombre   = limpiar_entrada($_POST['nombre']);
    $nickname = limpiar_entrada($_POST['nickname']);
    $email    = limpiar_entrada($_POST['email']);
    $pass     = $_POST['pass']; 

    if (user_existe($email)) {
        $resp["message"] = "Ese correo ya está registrado.";
    } else {
        $token = create_user_pendiente($nombre, $nickname, $email, 'editor', $pass);
        
        if ($token) {
            $mail = new PHPMailer(true);
            try {
                $m_host = get_opcion('mailer_host');
                $m_user = get_opcion('mailer_username');
                $m_pass = get_opcion('mailer_password');
                $m_port = get_opcion('mailer_port');

                $mail->isSMTP();
                $mail->Host       = $m_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $m_user;
                $mail->Password   = $m_pass;
                $mail->Port       = $m_port;
                $mail->CharSet    = 'UTF-8';
                $mail->SMTPSecure = ($m_port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                $link = "$protocol://{$_SERVER['HTTP_HOST']}/public/confirmar.php?token=$token";

                $mail->setFrom($m_user, 'Admin CMS BASE');
                $mail->addAddress($email, $nombre);

                $mail->isHTML(true);
                $mail->Subject = 'Confirma tu cuenta - CMS BASE';
                $mail->Body    = "
                    <div style='font-family: sans-serif; border: 1px solid #333; padding: 25px; border-radius: 10px; background: #f9f9f9;'>
                        <h2 style='color: #1db954;'>¡Hola $nombre!</h2>
                        <p>Gracias por registrarte. Haz clic en el botón para activar tu cuenta:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$link' style='background: #1db954; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>ACTIVAR MI CUENTA</a>
                        </div>
                    </div>";

                $mail->send();
                $resp = ["status" => "success", "message" => "Registro completado. Revisa tu email para activar la cuenta."];

            } catch (Exception $e) {
                $resp = ["status" => "success", "message" => "Usuario creado, pero hubo un error al enviar el email."];
            }
        } else {
            $resp["message"] = "Error al procesar el registro en la base de datos.";
        }
    }
}
echo json_encode($resp);