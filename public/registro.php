<?php
/* public/registro.php */
include_once __DIR__ . '/../api/main.php';

if (leer_opcion('registro') !== '1') {
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>Registro Cerrado</h1><p>El administrador ha deshabilitado los nuevos registros.</p></div>");
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
    <link rel="stylesheet" href="<?php echo recurso('assets/css/estilos.css'); ?>">
</head>
<body class="contenedor-auth">
    <div class="auth-card animated fadeIn">
        <div style="text-align:center; margin-bottom:20px;">
            <img src="<?php echo recurso('assets/images/iconos/logo.svg'); ?>" width="50" alt="Logo">
        </div>
        <h2>Únete a nosotros</h2>
        <p style="text-align:center; color:#666; margin-bottom:30px;">Crea tu cuenta en un paso.</p>

        <div id="mensaje"></div>

        <div id="contenedor-formulario">
            <form id="formRegistro">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label style="font-weight:700; font-size:0.8rem; color:#888;">NOMBRE COMPLETO</label>
                <input type="text" name="nombre" class="f-campo" placeholder="Ej: Pelín Dev" required>

                <label style="font-weight:700; font-size:0.8rem; color:#888;">NICKNAME</label>
                <input type="text" name="nickname" class="f-campo" placeholder="pelin_dev" required>

                <label style="font-weight:700; font-size:0.8rem; color:#888;">CORREO ELECTRÓNICO</label>
                <input type="email" name="email" class="f-campo" placeholder="tu@email.com" required>

                <label style="font-weight:700; font-size:0.8rem; color:#888;">CONTRASEÑA</label>
                <div style="position:relative;">
                    <input type="password" name="pass" id="pass_field" class="f-campo" placeholder="••••••••" required>
                    <button type="button" onclick="generarPassword()" style="position:absolute; right:10px; top:12px; background:#eee; border:none; padding:5px 10px; border-radius:5px; font-size:0.7rem; cursor:pointer; font-weight:bold;">SUGERIR</button>
                </div>

                <button type="submit" class="f-boton">CREAR MI CUENTA</button>
            </form>
        </div>

        <div style="margin-top:20px; text-align:center; font-size:0.9rem;">
            ¿Ya tienes cuenta? <a href="login.php" style="color:#7A006C; font-weight:bold; text-decoration:none;">Logueate</a>
        </div>
    </div>

    <script>
        function generarPassword() {
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
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
            msg.innerHTML = "<div class='txt-centro'>Procesando registro...</div>";
            
            fetch('../api/registro_proceso.php', { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    formDiv.style.display = 'none';
                    msg.innerHTML = `<div class="f-alerta f-exito">${data.message}</div>`;
                } else {
                    msg.innerHTML = `<div class="f-alerta f-error">${data.message}</div>`;
                }
            })
            .catch(error => {
                msg.innerHTML = `<div class="f-alerta f-error">Error en la conexión con el servidor.</div>`;
            });
        });
    </script>
</body>
</html>