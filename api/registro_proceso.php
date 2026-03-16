<?php
/* api/registro_proceso.php */
include_once __DIR__ . '/main.php';

// Cargamos el Autoload de Composer desde la carpeta tools
require __DIR__ . '/../tools/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$resp = ["status" => "error", "message" => "Error desconocido"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = limpiar_entrada($_POST['nombre']);
    $nickname = limpiar_entrada($_POST['nickname']);
    $email    = limpiar_entrada($_POST['email']);
    $pass     = $_POST['pass'];

    // 1. Validar si ya existe
    if (user_existe($email)) {
        $resp["message"] = "Ese correo ya está registrado.";
    } else {
        // 2. Crear usuario (rol editor por defecto)
        $user_id = create_user($nombre, $nickname, $email, 'editor', $pass);
        
        if ($user_id) {
            // 3. ENVÍO DE EMAIL CON PHPMAILER
            $mail = new PHPMailer(true);
            try {
                // Obtenemos configuración de la DB
                $m_host = get_opcion('mailer_host');
                $m_user = get_opcion('mailer_username');
                $m_pass = get_opcion('mailer_password');
                $m_port = get_opcion('mailer_port');

                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host       = $m_host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $m_user;
                $mail->Password   = $m_pass;
                $mail->Port       = $m_port;
                $mail->CharSet    = 'UTF-8';
                
                // Si el puerto es 465, activamos SMTPS (SSL)
                if ($m_port == 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }

                // Destinatarios
                $mail->setFrom($m_user, 'Admin CMS BASE');
                $mail->addAddress($email, $nombre);

                // Contenido
                $mail->isHTML(true);
                $mail->Subject = '¡Bienvenido a CMS BASE!';
                $mail->Body    = "
                    <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee;'>
                        <h2 style='color: #1db954;'>Hola $nombre,</h2>
                        <p>Tu cuenta ha sido creada con éxito.</p>
                        <p>Ya puedes acceder al panel con tu correo: <b>$email</b></p>
                        <hr>
                        <small>Atentamente, el equipo de CMS BASE.</small>
                    </div>";

                $mail->send();
                $resp = ["status" => "success", "message" => "Registro exitoso. Revisa tu email de bienvenida."];

            } catch (Exception $e) {
                // Usuario creado pero falló el mail
                $resp = ["status" => "success", "message" => "Usuario creado, pero no pudimos enviarte el correo de bienvenida."];
            }
        } else {
            $resp["message"] = "No se pudo crear el usuario en la base de datos.";
        }
    }
}

echo json_encode($resp);