<?php
/* public/registro.php */
include_once __DIR__ . '/../api/main.php';

if (get_opcion('registro') !== '1') {
    die("<h1>Registro Cerrado</h1><p>El administrador ha deshabilitado los nuevos registros.</p>");
}

if (isset($_SESSION['user_id'])) {
    header("Location: ../admin/admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta - CMS BASE</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f0f0f; color: #fff; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .reg-card { background: #181818; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; border: 1px solid #333; }
        h1 { color: #1db954; text-align: center; }
        input { width: 100%; padding: 12px; background: #252525; border: 1px solid #333; color: #fff; border-radius: 6px; margin-bottom: 15px; box-sizing: border-box; }
        .pass-container { position: relative; display: flex; align-items: center; }
        .btn-sugerir { position: absolute; right: 5px; background: #333; color: #1db954; border: none; padding: 5px 8px; cursor: pointer; border-radius: 4px; font-size: 0.7rem; top: 10px; }
        button[type="submit"] { width: 100%; padding: 14px; background: #1db954; border: none; font-weight: bold; border-radius: 30px; cursor: pointer; color: #000; margin-top: 10px; }
        .msg { text-align: center; margin-top: 15px; font-size: 0.9rem; line-height: 1.4; }
        .err { color: #ff5555; } .ok { color: #8fca9d; background: #1b3321; padding: 15px; border-radius: 8px; display: block; }
    </style>
</head>
<body>
    <div class="reg-card">
        <div id="contenedor-formulario">
            <h1>Únete a nosotros</h1>
            <form id="formRegistro">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <input type="text" name="nombre" placeholder="Nombre completo" required>
                <input type="text" name="nickname" placeholder="Nickname" required>
                <input type="email" name="email" placeholder="Correo electrónico" required>
                
                <div class="pass-container">
                    <input type="password" id="pass_field" name="pass" placeholder="Contraseña segura" required>
                    <button type="button" class="btn-sugerir" onclick="sugerirPass()">Sugerir</button>
                </div>
                
                <button type="submit">CREAR MI CUENTA</button>
            </form>
        </div>
        <div id="mensaje" class="msg"></div>
        <p style="text-align:center; font-size:0.8rem; margin-top:20px;">
            ¿Ya tienes cuenta? <a href="login.php" style="color:#1db954; text-decoration:none;">Inicia sesión</a>
        </p>
    </div>

    <script>
        function sugerirPass() {
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
            let pass = "";
            for (let i = 0; i < 16; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
            const field = document.getElementById('pass_field');
            field.value = pass;
            field.type = "text";
        }

        document.getElementById('formRegistro').addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = document.getElementById('mensaje');
            const formDiv = document.getElementById('contenedor-formulario');
            msg.innerHTML = "Procesando registro...";
            
            fetch('../api/registro_proceso.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    formDiv.style.display = 'none';
                    msg.innerHTML = `<span class="ok">${data.message}</span>`;
                } else {
                    msg.innerHTML = `<span class="err">${data.message}</span>`;
                }
            })
            .catch(error => {
                msg.innerHTML = `<span class="err">Error en la conexión con el servidor.</span>`;
            });
        });
    </script>
</body>
</html>