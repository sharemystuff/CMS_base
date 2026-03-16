<?php
/* public/registro.php */

include_once __DIR__ . '/../api/main.php';

// 1. Verificar si el registro está habilitado
if (get_opcion('registro') !== '1') {
    die("<h1>Registro Cerrado</h1><p>El administrador ha deshabilitado los nuevos registros. <a href='login.php'>Volver al login</a></p>");
}

// Si ya está logueado, no tiene sentido que se registre
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
        button { width: 100%; padding: 14px; background: #1db954; border: none; font-weight: bold; border-radius: 30px; cursor: pointer; color: #000; }
        .msg { text-align: center; margin-top: 15px; font-size: 0.9rem; }
        .err { color: #ff5555; } .ok { color: #1db954; }
    </style>
</head>
<body>
    <div class="reg-card">
        <h1>Únete a nosotros</h1>
        <form id="formRegistro">
            <input type="text" name="nombre" placeholder="Nombre completo" required>
            <input type="text" name="nickname" placeholder="Nickname (ej: pacheco)" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="pass" placeholder="Contraseña segura" required>
            <button type="submit">CREAR MI CUENTA</button>
        </form>
        <div id="mensaje" class="msg"></div>
        <p style="text-align:center; font-size:0.8rem; margin-top:20px;">
            ¿Ya tienes cuenta? <a href="login.php" style="color:#1db954;">Inicia sesión</a>
        </p>
    </div>

    <script>
        document.getElementById('formRegistro').addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = document.getElementById('mensaje');
            msg.innerHTML = "Procesando registro...";
            
            fetch('../api/registro_proceso.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    msg.innerHTML = `<span class="ok">${data.message}</span>`;
                    setTimeout(() => { window.location.href = 'login.php'; }, 2000);
                } else {
                    msg.innerHTML = `<span class="err">${data.message}</span>`;
                }
            });
        });
    </script>
</body>
</html>