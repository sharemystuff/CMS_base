<?php
/* api/login_proceso.php */
include_once __DIR__ . '/main.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpiar_entrada($_POST['email']);
    $pass  = $_POST['password'];
    $recuerdame = isset($_POST['recuerdame']);

    // 1. Verificar que la conexión existe
    if (!$conexion) { die("Error de conexión a la base de datos."); }

    // 2. Buscamos al usuario
    $stmt = $conexion->prepare("SELECT id, password, nombre, activo, rol FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($u = $resultado->fetch_assoc()) {
        
        // 3. Verificar si la cuenta está activa
        if ($u['activo'] != 1) {
            header("Location: ../public/login.php?error=not_active");
            exit;
        }

        // --- TEST DE SEGURIDAD ---
        $salt = get_opcion('salt_key');
        if(!$salt) { die("Error fatal: No se pudo recuperar la llave de seguridad (salt_key)."); }
        // -------------------------

        // 4. Verificar contraseña
        if (encode_pass($pass) === $u['password']) {
            
            // ÉXITO: Iniciamos sesión
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_nombre'] = $u['nombre'];
            $_SESSION['user_rol'] = $u['rol'];

            // Cookie "Recuérdame"
            if ($recuerdame) {
                $token = bin2hex(random_bytes(32));
                $dias = get_opcion('recuerdame') ? (int)get_opcion('recuerdame') : 30;
                $stmt_token = $conexion->prepare("UPDATE usuarios SET session_token = ? WHERE id = ?");
                $stmt_token->bind_param("si", $token, $u['id']);
                $stmt_token->execute();
                setcookie('session_token', $token, time() + (86400 * $dias), "/", "", false, true);
            }

            header("Location: ../admin/admin.php");
            exit;

        } else {
            // Contraseña no coincide
            header("Location: ../public/login.php?error=1");
            exit;
        }
    } else {
        // Usuario no existe
        header("Location: ../public/login.php?error=1");
        exit;
    }
} else {
    header("Location: ../public/login.php");
    exit;
}