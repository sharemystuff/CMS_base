<?php
/* public/login.php */
session_start();
// Si ya hay sesión, mandamos al admin directamente
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
        .login-card { background: #181818; padding: 40px; border-radius: 12px; width: 100%; max-width: 350px; border: 1px solid #333; box-shadow: 0 15px 35px rgba(0,0,0,0.7); }
        h1 { color: #1db954; text-align: center; margin-bottom: 30px; font-size: 1.8rem; }
        .field { margin-bottom: 20px; }
        label { display: block; font-size: 0.8rem; color: #aaa; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; background: #252525; border: 1px solid #333; color: #fff; border-radius: 6px; box-sizing: border-box; }
        input:focus { border-color: #1db954; outline: none; }
        button { width: 100%; padding: 14px; background: #1db954; border: none; color: #000; font-weight: bold; border-radius: 6px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #1ed760; }
        #mensaje { text-align: center; margin-top: 20px; font-size: 0.9rem; min-height: 1.2rem; }
        .err { color: #ff5555; }
        .ok { color: #1db954; }
    </style>
</head>
<body>

    <div class="login-card">
        <h1>CMS BASE</h1>
        <form id="formLogin">
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" id="email" required placeholder="tu@email.com">
            </div>
            <div class="field">
                <label>Contraseña</label>
                <input type="password" name="pass" id="pass" required placeholder="••••••••">
            </div>
            <button type="submit">ENTRAR AL PANEL</button>
        </form>
        <div id="mensaje"></div>
    </div>

    <script>
        document.getElementById('formLogin').addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = document.getElementById('mensaje');
            msg.innerHTML = "Procesando...";
            
            const formData = new FormData(this);
            
            fetch('../api/login_proceso.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    msg.innerHTML = `<span class="ok">${data.message}. Redirigiendo...</span>`;
                    setTimeout(() => { window.location.href = '../admin/admin.php'; }, 1500);
                } else {
                    msg.innerHTML = `<span class="err">${data.message}</span>`;
                }
            })
            .catch(error => {
                msg.innerHTML = `<span class="err">Error en la comunicación con el servidor.</span>`;
            });
        });
    </script>
</body>
</html>