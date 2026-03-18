<?php
/* api/login_proceso.php */
include_once __DIR__ . '/main.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpiar_entrada($_POST['email'] ?? '');
    $pass = limpiar_entrada($_POST['pass'] ?? '', true); 
    $recuerdame = isset($_POST['recuerdame']);

    if (!$conexion) {
        echo json_encode(["status" => "error", "message" => "Error de conexión interna."]);
        exit;
    }

    // Usamos la función centralizada traducida
    if (iniciar_sesion($email, $pass)) {
        
        if ($recuerdame) {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $dias_opc = leer_opcion('recuerdame');
            $dias = $dias_opc ? (int)$dias_opc : 30;
            
            $stmt_token = $conexion->prepare('UPDATE usuarios SET session_token = ? WHERE id = ?');
            $stmt_token->bind_param('si', $token_hash, $_SESSION['user_id']);
            $stmt_token->execute();
            
            $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            setcookie('session_token', $token, time() + (86400 * $dias), "/", "", $is_secure, true);
        }
        
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Correo o contraseña incorrectos."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
}