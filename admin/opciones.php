<?php
/* admin/opciones.php */
include_once '../api/main.php';
checking(); // Seguridad Mangiacaprini

$mensaje = "";

// Procesar Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_options'])) {
    update_opcion('recuerdame', limpiar_entrada($_POST['recuerdame']));
    update_opcion('registro', isset($_POST['registro']) ? '1' : '0');
    update_opcion('mailer_host', limpiar_entrada($_POST['mailer_host']));
    update_opcion('mailer_username', limpiar_entrada($_POST['mailer_username']));
    update_opcion('mailer_password', $_POST['mailer_password']); // Password de correo no se limpia igual
    update_opcion('mailer_port', limpiar_entrada($_POST['mailer_port']));
    
    $mensaje = "✅ Opciones actualizadas correctamente.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración General - CMS BASE</title>
    <link rel="stylesheet" href="css/themify-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <?php include_once 'sec-header.php'; ?>

    <div class="main-wrapper" style="display: flex;">
        <?php include_once 'sec-aside.php'; ?>

        <main class="content-area" style="padding: 30px; flex-grow: 1;">
            <h1>Configuración del Sistema</h1>
            
            <?php if($mensaje): ?>
                <div style="background: #1b3321; color: #88ffaa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST" style="max-width: 600px; background: #181818; padding: 30px; border-radius: 10px; border: 1px solid #333;">
                
                <h3>Acceso y Seguridad</h3>
                <label>Días de "Recuérdame":</label>
                <input type="number" name="recuerdame" value="<?php echo get_opcion('recuerdame'); ?>" style="display:block; width:100%; padding:10px; margin: 10px 0; background:#252525; border:1px solid #444; color:#fff;">
                
                <label style="display: flex; align-items: center; cursor: pointer; margin: 20px 0;">
                    <input type="checkbox" name="registro" <?php echo (get_opcion('registro') == '1') ? 'checked' : ''; ?> style="margin-right: 10px;">
                    Habilitar Registro de Usuarios Público
                </label>

                <hr style="border:0; border-top: 1px solid #333; margin: 30px 0;">

                <h3>Configuración SwiftMailer (SMTP)</h3>
                <label>Servidor SMTP (Host):</label>
                <input type="text" name="mailer_host" value="<?php echo get_opcion('mailer_host'); ?>" placeholder="smtp.tuproveedor.com" style="display:block; width:100%; padding:10px; margin: 10px 0; background:#252525; border:1px solid #444; color:#fff;">
                
                <label>Usuario Correo:</label>
                <input type="text" name="mailer_username" value="<?php echo get_opcion('mailer_username'); ?>" placeholder="info@tusitio.com" style="display:block; width:100%; padding:10px; margin: 10px 0; background:#252525; border:1px solid #444; color:#fff;">
                
                <label>Contraseña Correo:</label>
                <input type="password" name="mailer_password" value="<?php echo get_opcion('mailer_password'); ?>" style="display:block; width:100%; padding:10px; margin: 10px 0; background:#252525; border:1px solid #444; color:#fff;">
                
                <label>Puerto SMTP:</label>
                <input type="text" name="mailer_port" value="<?php echo get_opcion('mailer_port'); ?>" placeholder="465 o 587" style="display:block; width:100%; padding:10px; margin: 10px 0; background:#252525; border:1px solid #444; color:#fff;">

                <button type="submit" name="save_options" style="margin-top:20px; padding: 12px 30px; background: #1db954; border:none; border-radius: 5px; font-weight:bold; cursor:pointer;">GUARDAR CAMBIOS</button>
            </form>
        </main>
    </div>

    <?php include_once 'sec-footer.php'; ?>
</body>
</html>