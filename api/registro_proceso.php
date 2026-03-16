<?php
/* api/registro_proceso.php */
include_once __DIR__ . '/main.php';

// RUTA EXACTA según tu repositorio:
// Subimos un nivel (..) para salir de 'api', entramos en 'tools' y seguimos la ruta.
require_once __DIR__ . '/../tools/vendor/swiftmailer/swiftmailer/lib/swift_required.php';

$resp = ["status" => "error", "message" => "Error desconocido"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = limpiar_entrada($_POST['nombre']);
    $nickname = limpiar_entrada($_POST['nickname']);
    $email    = limpiar_entrada($_POST['email']);
    $pass     = $_POST['pass'];

    // 1. Validar si ya existe el usuario
    if (user_existe($email)) {
        $resp["message"] = "Ese correo ya está registrado.";
    } else {
        // 2. Crear usuario (rol editor por defecto)
        $user_id = create_user($nombre, $nickname, $email, 'editor', $pass);
        
        if ($user_id) {
            // 3. INTENTO DE ENVÍO DE EMAIL CON SWIFTMAILER
            try {
                // Obtenemos configuración de la DB (previamente guardada en admin/opciones.php)
                $m_host = get_opcion('mailer_host');
                $m_user = get_opcion('mailer_username');
                $m_pass = get_opcion('mailer_password');
                $m_port = get_opcion('mailer_port');

                // Configurar transporte SMTP
                // Nota: SwiftMailer detecta automáticamente si usar SSL/TLS según el puerto
                $transport = (new Swift_SmtpTransport($m_host, $m_port))
                  ->setUsername($m_user)
                  ->setPassword($m_pass);

                $mailer = new Swift_Mailer($transport);

                // Crear el mensaje de bienvenida
                $message = (new Swift_Message('¡Bienvenido a CMS BASE!'))
                  ->setFrom([$m_user => 'Admin CMS BASE'])
                  ->setTo([$email => $nombre])
                  ->setBody("
                    <h2>Hola $nombre,</h2>
                    <p>Tu cuenta ha sido creada con éxito en el sistema.</p>
                    <p>Tus datos de acceso son:</p>
                    <ul>
                        <li><strong>Email:</strong> $email</li>
                    </ul>
                    <p>Ya puedes iniciar sesión desde el panel de control.</p>
                  ", 'text/html');

                // Enviar
                $result = $mailer->send($message);
                
                $resp = ["status" => "success", "message" => "Registro exitoso. ¡Revisa tu bandeja de entrada!"];
                
            } catch (Exception $e) {
                // El usuario se creó, pero el envío de correo falló (probablemente por config SMTP)
                // Guardamos el error en el log del servidor para debuggear si es necesario
                error_log("Error SwiftMailer: " . $e->getMessage());
                $resp = ["status" => "success", "message" => "Usuario creado, pero hubo un problema al enviar el correo de bienvenida."];
            }
        } else {
            $resp["message"] = "Error crítico: No se pudo insertar el usuario en la base de datos.";
        }
    }
}

echo json_encode($resp);