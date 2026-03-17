<?php
/* public/login.php */
include_once __DIR__ . '/../api/main.php';

if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

$error = "";
$ip_actual = get_client_ip();

// 1. Verificar si la IP ya superó los 5 intentos en los últimos 5 min
$intentos = contar_intentos_fallidos($ip_actual);
$limite_intentos = 5;

if ($intentos >= $limite_intentos) {
    $error = "Demasiados intentos. Por seguridad, tu acceso ha sido bloqueado temporalmente. Vuelve a intentarlo en 5 minutos.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $intentos < $limite_intentos) {
    if (!isset($_POST['csrf_token']) || !validarCSRF($_POST['csrf_token'])) {
        die("Sesión inválida.");
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];
    $recuerdame = isset($_POST['recuerdame']);

    $stmt = $conexion->prepare("SELECT id, nombre, nickname, password, rol, activo FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($user['activo'] != 1) {
            $error = "Cuenta inactiva. Revisa tu email.";
        } 
        elseif (password_verify($pass, $user['password'])) {
            // ÉXITO: Limpiamos historial de fallos de esta IP
            limpiar_intentos_ip($ip_actual);
            
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];

            if ($recuerdame) {
                $token_real = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token_real);
                $dias = (int)($OPC['recuerdame'] ?? 14);
                $expira = time() + ($dias * 24 * 60 * 60);

                $upd = $conexion->prepare("UPDATE usuarios SET session_token = ? WHERE id = ?");
                $upd->bind_param("si", $token_hash, $user['id']);
                $upd->execute();

                setcookie('session_token', $token_real, [
                    'expires' => $expira,
                    'path' => '/',
                    'domain' => '', 
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
            header("Location: ../admin/admin.php");
            exit;
        } else {
            // FALLO: Registramos IP y Email
            registrar_intento_fallido($ip_actual, $email);
            $error = "Credenciales incorrectas.";
        }
    } else {
        // FALLO: Email inexistente
        registrar_intento_fallido($ip_actual, $email);
        $error = "Credenciales incorrectas.";
    }
    sleep(1); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - CMS BASE</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #e0e0e0; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: #1e1e1e; padding: 40px; border-radius: 12px; width: 100%; max-width: 350px; border: 1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #1db954; text-align: center; margin-bottom: 25px; }
        label { display: block; margin-bottom: 5px; font-size: 0.8rem; color: #888; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #333; background: #2a2a2a; color: #fff; border-radius: 4px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #1db954; }
        input:disabled { background: #1a1a1a; cursor: not-allowed; opacity: 0.5; }
        .recuerdame-box { margin-bottom: 15px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; }
        button { width: 100%; padding: 12px; background: #1db954; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; color: #000; transition: 0.3s; }
        button:hover:not(:disabled) { background: #1ed760; }
        button:disabled { background: #444; color: #888; cursor: not-allowed; }
        .error { color: #ff5555; font-size: 0.8rem; text-align: center; margin-bottom: 15px; background: rgba(255, 85, 85, 0.1); padding: 10px; border-radius: 4px; border: 1px solid #ff5555; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <label>Email</label>
            <input type="email" name="email" required autocomplete="off" <?php if($intentos >= $limite_intentos) echo 'disabled'; ?>>
            
            <label>Contraseña</label>
            <input type="password" name="password" required autocomplete="off" <?php if($intentos >= $limite_intentos) echo 'disabled'; ?>>
            
            <div class="recuerdame-box">
                <input type="checkbox" name="recuerdame" id="recuerdame" <?php if($intentos >= $limite_intentos) echo 'disabled'; ?>>
                <label for="recuerdame" style="display:inline; color:#ccc; cursor:pointer;">Recuérdame</label>
            </div>

            <button type="submit" <?php if($intentos >= $limite_intentos) echo 'disabled'; ?>>ENTRAR</button>
        </form>
        <p style="text-align:center; font-size:0.8rem; margin-top:20px;">
            <a href="recuperar.php" style="color:#888; text-decoration:none;">¿Olvidaste tu contraseña?</a>
        </p>
    </div>
</body>
</html>