<?php
/* api/login_proceso.php */
session_start();
include_once __DIR__ . '/db.php';
include_once __DIR__ . '/../seguridad/funciones.php';
include_once __DIR__ . '/../tovi/funciones.php';

$respuesta = ["status" => "error", "message" => "Acceso denegado"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpiar_entrada($_POST['email'] ?? '');
    $pass  = $_POST['pass'] ?? '';
    $remember = isset($_POST['remember']);
    $ip = $_SERVER['REMOTE_ADDR'];

    // [Lógica de bloqueo omitida aquí por brevedad, pero debe mantenerse la de antes]
    
    $stmt = $conexion->prepare("SELECT id, password, nombre, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && verificar_pass($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nombre'] = $user['nombre'];
        $_SESSION['user_rol'] = $user['rol'];

        if ($remember) {
            // Buscamos cuántos días durará la sesión en la tabla opciones
            $res_opt = $conexion->query("SELECT opcion_dato FROM opciones WHERE opcion_key = 'recuerdame'");
            $dias = ($res_opt->num_rows > 0) ? (int)$res_opt->fetch_assoc()['opcion_dato'] : 30;
            
            // Generamos Token de Persistencia
            $token = bin2hex(random_bytes(32));
            $stmt_token = $conexion->prepare("UPDATE usuarios SET session_token = ? WHERE id = ?");
            $stmt_token->bind_param("si", $token, $user['id']);
            $stmt_token->execute();

            // Seteamos la Cookie (Segura y HttpOnly)
            setcookie('pelin_remember', $token, time() + (86400 * $dias), "/", "", true, true);
        }

        $respuesta = ["status" => "success", "message" => "Bienvenido"];
    } else {
        $respuesta = ["status" => "error", "message" => "Datos incorrectos"];
    }
}
echo json_encode($respuesta);