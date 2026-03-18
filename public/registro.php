<?php
/* public/registro.php */
include_once __DIR__ . '/../api/main.php';

if (leer_opcion('registro') !== '1') {
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>Registro Cerrado</h1></div>");
}

if (sesion_activa()) {
    header("Location: ../admin/admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body class="flex-center">

    <div class="registro-card animated fadeIn">
        <div class="txt-centro" style="margin-bottom:20px;">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="50" alt="Logo">
        </div>
        
        <h1 class="txt-centro">Únete a nosotros</h1>
        <p class="txt-centro" style="color:#666; margin-bottom:30px;">Crea tu cuenta en un paso.</p>

        <div id="mensaje"></div>

        <form id="formRegistro">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label>Nombre Completo</label>
            <input type="text" name="nombre" class="campo" required>

            <label>Nickname</label>
            <input type="text" name="nickname" class="campo" required>

            <label>Email</label>
            <input type="email" name="email" class="campo" required>

            <label>Contraseña</label>
            <div class="pass-container">
                <input type="password" name="pass" id="pass_field" class="campo" required>
                <button type="button" class="btn-sugerir" onclick="generarPassword()">SUGERIR</button>
            </div>

            <button type="submit" class="boton-principal">CREAR MI CUENTA</button>
        </form>

        <div class="txt-centro" style="margin-top:20px;">
            ¿Ya tienes cuenta? <a href="login.php" style="color:var(--primario); font-weight:bold;">Logueate</a>
        </div>
    </div>

    <script>
        function generarPassword() {
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
            let pass = "";
            for (let i = 0; i < 16; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
            document.getElementById('pass_field').value = pass;
            document.getElementById('pass_field').type = "text";
        }

        document.getElementById('formRegistro').addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = document.getElementById('mensaje');
            msg.innerHTML = "Procesando...";
            
            fetch('../api/registro_proceso.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('formRegistro').style.display = 'none';
                    msg.innerHTML = `<div class="alerta alerta-exito">${data.message}</div>`;
                } else {
                    msg.innerHTML = `<div class="alerta alerta-error">${data.message}</div>`;
                }
            });
        });
    </script>
</body>
</html>