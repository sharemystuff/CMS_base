<?php
/* public/login.php */
include_once __DIR__ . '/../api/main.php';

// Si ya está logueado, al admin
if (isset($_SESSION['user_id'])) {
    header("Location: ../admin/admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CMS BASE</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-card { background: #181818; padding: 40px; border-radius: 12px; width: 100%; max-width: 350px; border: 1px solid #333; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
        h1 { color: #1db954; text-align: center; margin-bottom: 30px; }
        input { width: 100%; padding: 12px; background: #252525; border: 1px solid #333; color: #fff; border-radius: 6px; margin-bottom: 15px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #1db954; }
        button { width: 100%; padding: 14px; background: #1db954; border: none; font-weight: bold; border-radius: 30px; cursor: pointer; color: #000; transition: 0.3s; }
        button:hover { background: #1ed760; transform: scale(1.02); }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.85rem; text-align: center; line-height: 1.4; }
        .alert-error { background: #441111; color: #ff9999; border: 1px solid #ff5555; }
        .alert-success { background: #1b3321; color: #8fca9d; border: 1px solid #1db954; }
        .footer-links { text-align: center; margin-top: 20px; font-size: 0.85rem; color: #888; }
        .footer-links a { color: #1db954; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>CMS BASE</h1>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                    if($_GET['error'] === 'not_active') {
                        echo "<strong>¡Acceso restringido!</strong><br>Debes activar tu cuenta desde el email que te enviamos.";
                    } else {
                        echo "Email o contraseña incorrectos.";
                    }
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['success']) && $_GET['success'] === 'activated'): ?>
            <div class="alert alert-success">
                ✅ ¡Cuenta activada con éxito!<br>Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <form action="../api/login_proceso.php" method="POST">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <label style="font-size: 0.8rem; display: block; margin-bottom: 15px; cursor: pointer; color: #bbb;">
                <input type="checkbox" name="recuerdame" style="width: auto; margin-right: 5px;"> Mantener sesión iniciada
            </label>
            <button type="submit">ENTRAR</button>
        </form>

        <div class="footer-links">
            <a href="recuperar.php">¿Olvidaste tu contraseña?</a>
            
            <?php if (get_opcion('registro') === '1'): ?>
                <div style="margin-top: 15px; border-top: 1px solid #333; padding-top: 15px;">
                    ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>