<?php
/* public/login.php */
session_start();
if (isset($_SESSION['user_id'])) { header("Location: ../admin/admin.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - CMS BASE</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-card { background: #181818; padding: 40px; border-radius: 12px; width: 100%; max-width: 350px; border: 1px solid #333; }
        h1 { color: #1db954; text-align: center; }
        input[type="email"], input[type="password"] { width: 100%; padding: 12px; background: #252525; border: 1px solid #333; color: #fff; border-radius: 6px; margin-bottom: 20px; box-sizing: border-box; }
        .remember { display: flex; align-items: center; font-size: 0.8rem; color: #aaa; margin-bottom: 20px; }
        .remember input { margin-right: 10px; }
        button { width: 100%; padding: 14px; background: #1db954; border: none; font-weight: bold; border-radius: 6px; cursor: pointer; }
        #mensaje { text-align: center; margin-top: 20px; }
        .err { color: #ff5555; } .ok { color: #1db954; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>CMS BASE</h1>
        <form id="formLogin">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="pass" placeholder="Password" required>
            <label class="remember">
                <input type="checkbox" name="remember"> Recuérdame en este equipo
            </label>
            <button type="submit">ENTRAR</button>
        </form>
        <div id="mensaje"></div>
    </div>
    <script>
        document.getElementById('formLogin').addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = document.getElementById('mensaje');
            msg.innerHTML = "Validando...";
            fetch('../api/login_proceso.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    msg.innerHTML = `<span class="ok">${data.message}</span>`;
                    setTimeout(() => { window.location.href = '../admin/admin.php'; }, 1000);
                } else { msg.innerHTML = `<span class="err">${data.message}</span>`; }
            });
        });
    </script>
</body>
</html>