<?php
/* api/registro_proceso.php */
include_once __DIR__ . '/main.php';

// Cargamos el Autoload de Composer
require __DIR__ . '/../tools/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$resp = ["status" => "error", "message" => "Error desconocido"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = limpiar_entrada($_POST['nombre']);
    $nickname = limpiar_entrada($_POST['nickname']);
    $email    = limpiar_entrada($_POST['email']);
    $pass     = $_POST['pass']; // Recibimos 'pass' desde el formulario

    if (user_existe($email)) {
        $resp["message"] = "Ese correo ya está registrado.";
    } else {
        // Creamos el usuario inactivo y obtenemos el token
        $token = create_user_pendiente($nombre, $nickname, $email, 'editor', $pass);
        
        if ($token) {
            $mail = new PHPMailer(true);
            try {
                $m_host = get_opcion('mailer_host');
                $m_user = get_opcion('mailer_username');
                $m_pass = get_opcion('mailer_password');
                $m_port = get_opcion('mailer_port');

                // Configuración PHPMailer
                $mail->isSMTP();
                $mail->Host       = $m_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $m_user;
                $mail->Password   = $m_pass;
                $mail->Port       = $m_port;
                $mail->CharSet    = 'UTF-8';
                $mail->SMTPSecure = ($m_port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

                // Construcción del enlace de activación
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                $link = "$protocol://{$_SERVER['HTTP_HOST']}/public/confirmar.php?token=$token";

                $mail->setFrom($m_user, 'Admin CMS BASE');
                $mail->addAddress($email, $nombre);

                $mail->isHTML(true);
                $mail->Subject = 'Confirma tu cuenta - CMS BASE';
                $mail->Body    = "
                    <div style='font-family: sans-serif; border: 1px solid #333; padding: 25px; border-radius: 10px; background: #f9f9f9;'>
                        <h2 style='color: #1db954;'>¡Hola $nombre!</h2>
                        <p>Gracias por registrarte. Para activar tu cuenta y poder acceder al panel, haz clic en el siguiente botón:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='$link' style='background: #1db954; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>ACTIVAR MI CUENTA</a>
                        </div>
                        <p style='font-size: 0.8rem; color: #666;'>Si el botón no funciona, copia y pega este enlace en tu navegador:<br>$link</p>
                    </div>";

                $mail->send();
                $resp = ["status" => "success", "message" => "¡Registro casi completado! Te hemos enviado un correo de confirmación. Por favor, revisa tu bandeja de entrada para activar tu cuenta."];

            } catch (Exception $e) {
                // En caso de error de mail, el usuario ya existe en DB, así que notificamos el éxito parcial
                $resp = ["status" => "success", "message" => "Usuario creado, pero hubo un error al enviar el email. Contacta al soporte para la activación manual."];
            }
        } else {
            $resp["message"] = "Error al procesar el registro en la base de datos.";
        }
    }
}
echo json_encode($resp);