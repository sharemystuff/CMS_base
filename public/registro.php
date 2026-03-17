<?php
/* public/registro.php */
include_once __DIR__ . '/../api/main.php';

if (get_opcion('registro') !== '1') {
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>Registro Cerrado</h1><p>El administrador ha deshabilitado los nuevos registros.</p></div>");
}

if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Cuenta - CMS BASE</title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
    <style>
        .pass-container { position: relative; }
        .btn-sugerir { 
            position: absolute; 
            right: 8px; 
            top: 8px; 
            background: var(--ui); 
            color: var(--primario); 
            border: none; 
            padding: 5px 10px; 
            cursor: pointer; 
            border-radius: 4px; 
            font-size: 0.7rem; 
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-sugerir:hover { background: var(--primario); color: white; }
    </style>
</head>
<body style="display:flex; align-items:center; min-height:100vh;">
    <div class="caja">
        <div class="txt-centro">
            <img src="<?php echo asset('assets/images/icons/logo.svg'); ?>" width="60" alt="Logo" style="margin-bottom:10px;">
        </div>
        
        <div id="contenedor-formulario">
            <h1>Únete a nosotros</h1>
            <p class="txt-centro" style="font-size: 0.9rem; color: #666; margin-bottom: 20px;">Crea tu cuenta de staff para empezar.</p>
            
            <form id="formRegistro">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <label>Nombre Completo</label>
                <input type="text" name="nombre" class="campo" placeholder="Ej. Pelín" required>
                
                <label>Nickname</label>
                <input type="text" name="nickname" class="campo" placeholder="pelin_dev" required>
                
                <label>Correo Electrónico</label>
                <input type="email" name="email" class="campo" placeholder="correo@ejemplo.com" required>
                
                <label>Contraseña Segura</label>
                <div class="pass-container">
                    <input type="password" id="pass_field" name="pass" class="campo" placeholder="••••••••" required>
                    <button type="button" class="btn-sugerir" onclick="sugerirPass()">Sugerir</button>
                </div>
                
                <button type="submit" class="boton" style="margin-top:10px;">CREAR MI CUENTA</button>
            </form>
        </div>

        <div id="mensaje" class="msg" style="margin-top:20px;"></div>

        <p class="txt-centro" style="margin-top:20px; font-size:0.8rem;">
            ¿Ya tienes cuenta? <a href="login.php" class="enlace">Inicia sesión</a>
        </p>
    </div>

    <script>
        function sugerirPass() {
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
            
            fetch('../api/registro_proceso.php', { 
                method: 'POST', 
                body: new FormData(this) 
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    formDiv.style.display = 'none';
                    msg.innerHTML = `<div class="alerta alerta-exito">${data.message}</div>`;
                } else {
                    msg.innerHTML = `<div class="alerta alerta-error">${data.message}</div>`;
                }
            })
            .catch(error => {
                msg.innerHTML = `<div class="alerta alerta-error">Error en la conexión con el servidor.</div>`;
            });
        });
    </script>
</body>
</html>