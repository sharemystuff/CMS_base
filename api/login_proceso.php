<?php
/* api/login_proceso.php */
include_once __DIR__ . '/main.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpiar_entrada($_POST['email']);
    $pass  = $_POST['password'];
    $recuerdame = isset($_POST['recuerdame']);

    // 1. Buscamos al usuario
    $stmt = $conexion->prepare("SELECT id, password, nombre, activo, rol FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($u = $resultado->fetch_assoc()) {
        
        // 2. Verificar si la cuenta está activa
        if ($u['activo'] != 1) {
            die("<h1>Acceso denegado</h1><p>Tu cuenta aún no ha sido activada. Revisa tu correo electrónico. <a href='../public/login.php'>Volver</a></p>");
        }

        // 3. Verificar contraseña (usando nuestra función de seguridad)
        if (encode_pass($pass) === $u['password']) {
            
            // Login exitoso: Iniciamos sesión
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_nombre'] = $u['nombre'];
            $_SESSION['user_rol'] = $u['rol'];

            // 4. Lógica de "Recuérdame" (Cookies)
            if ($recuerdame) {
                // Generamos un token único de sesión
                $token = bin2hex(random_bytes(32));
                $dias_expira = get_opcion('recuerdame') ? (int)get_opcion('recuerdame') : 30;
                
                // Guardamos el token en la base de datos para este usuario
                $stmt_token = $conexion->prepare("UPDATE usuarios SET session_token = ? WHERE id = ?");
                $stmt_token->bind_param("si", $token, $u['id']);
                $stmt_token->execute();

                // Seteamos la cookie en el navegador (expira según la opción guardada)
                setcookie('session_token', $token, time() + (86400 * $dias_expira), "/", "", false, true);
            }

            // Redirigir al panel de administración
            header("Location: ../admin/admin.php");
            exit;

        } else {
            // Contraseña incorrecta
            header("Location: ../public/login.php?error=1");
            exit;
        }
    } else {
        // Usuario no encontrado
        header("Location: ../public/login.php?error=1");
        exit;
    }
} else {
    // Si intentan entrar por URL sin POST
    header("Location: ../public/login.php");
    exit;
}