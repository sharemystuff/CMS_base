<?php
/* api/registro_proceso.php */
include_once __DIR__ . '/main.php';

$resp = ["status" => "error", "message" => "Solicitud no autorizada"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $token_recibido = $_POST['csrf_token'] ?? '';
    if (!validarCSRF($token_recibido)) {
        http_response_code(403);
        echo json_encode(["status" => "error", "message" => "Token de seguridad inválido"]);
        exit;
    }

    $nombre   = limpiar_entrada($_POST['nombre'] ?? '');
    $nickname = limpiar_entrada($_POST['nickname'] ?? '');
    $email    = limpiar_entrada($_POST['email'] ?? '');
    $pass     = $_POST['pass'] ?? ''; 

    if (empty($nombre) || empty($email) || empty($pass)) {
        $resp["message"] = "Todos los campos son obligatorios.";
    } elseif (usuario_existe($email)) { 
        $resp["message"] = "Ese correo ya está registrado.";
    } else {
        $token = crear_usuario_temporal($nombre, $nickname, $email, 'user', $pass);
        
        if ($token) {
            $link = url_base() . "/public/confirmar.php?token=" . $token;
            $asunto = "Activa tu cuenta en CMS BASE";
            $cuerpo = "<h2>¡Hola $nombre!</h2><p>Gracias por unirte. Haz clic aquí para activar tu cuenta: <a href='$link'>Activar Cuenta</a></p>";

            if (function_exists('enviar_email') && enviar_email($email, $asunto, $cuerpo)) {
                $resp = ["status" => "success", "message" => "Registro completado. Revisa tu email para activar la cuenta."];
            } else {
                $resp = ["status" => "success", "message" => "Usuario creado (confirmación manual requerida)."];
            }
        } else {
            $resp["message"] = "Error interno al crear el usuario.";
        }
    }
}

header('Content-Type: application/json');
echo json_encode($resp);