<?php
/* public/login.php */
include_once '../api/main.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $error = "Cuenta inactiva.";
        } 
        elseif (password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_nickname'] = $user['nickname'];
            $_SESSION['user_rol'] = $user['rol'];

            // LÓGICA DE RECUÉRDAME CON HASH (Extrema Seguridad)
            if ($recuerdame) {
                $token_real = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token_real); // Solo el hash va a la DB
                
                $dias = (int)($OPC['recuerdame'] ?? 14);
                $expira = time() + ($dias * 24 * 60 * 60);

                // Guardar el HASH en la base de datos
                $upd = $conexion->prepare("UPDATE usuarios SET session_token = ? WHERE id = ?");
                $upd->bind_param("si", $token_hash, $user['id']);
                $upd->execute();

                // Enviar el TOKEN REAL en la cookie
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
            $error = "Credenciales incorrectas.";
        }
    } else {
        $error = "Credenciales incorrectas.";
    }
    sleep(2); // Mitigación de fuerza bruta
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
        .recuerdame-box { margin-bottom: 15px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; }
        button { width: 100%; padding: 12px; background: #1db954; border: none; font-weight: bold; cursor: pointer; border-radius: 4px; color: #000; transition: 0.3s; }
        button:hover { background: #1ed760; }
        .error { color: #ff5555; font-size: 0.8rem; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE</h1>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label>Email</label>
            <input type="email" name="email" required autocomplete="off">
            <label>Contraseña</label>
            <input type="password" name="password" required autocomplete="off">
            
            <div class="recuerdame-box">
                <input type="checkbox" name="recuerdame" id="recuerdame">
                <label for="recuerdame" style="display:inline; color:#ccc; cursor:pointer;">Recuérdame</label>
            </div>

            <button type="submit">ENTRAR</button>
        </form>
    </div>
</body>
</html>