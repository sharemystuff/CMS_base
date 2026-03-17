<?php
/* api/registro_proceso.php */
include_once __DIR__ . '/main.php';

$resp = ["status" => "error", "message" => "Solicitud no autorizada"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // VALIDACIÓN CSRF
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
            $base_url = get_opcion('url_sitio');
            $link = $base_url . "/public/confirmar.php?token=" . $token;
            $asunto = 'Confirma tu cuenta - CMS BASE';
            $cuerpo = "
                <div style='font-family: sans-serif; border: 1px solid #333; padding: 25px; border-radius: 10px; background: #ffffff; color: #333;'>
                    <h2 style='color: #7A006C;'>¡Hola $nombre!</h2>
                    <p>Gracias por registrarte en nuestro sistema. Para empezar a usar tu cuenta, por favor confírmala haciendo clic en el siguiente botón:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='$link' style='background: #7A006C; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>ACTIVAR MI CUENTA</a>
                    </div>
                    <p style='font-size: 0.8rem; color: #888;'>Si el botón no funciona, copia y pega este enlace en tu navegador:<br>$link</p>
                </div>";

            if (enviar_email($email, $asunto, $cuerpo)) {
                $resp = ["status" => "success", "message" => "Registro completado. Revisa tu email para activar la cuenta."];
            } else {
                $resp = ["status" => "success", "message" => "Usuario creado, pero hubo un error al enviar el email de activación."];
            }
        } else {
            $resp["message"] = "Error al procesar el registro en la base de datos.";
        }
    }
}
echo json_encode($resp);