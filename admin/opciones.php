<?php
/* admin/opciones.php */
include_once __DIR__ . '/../api/main.php';

restringir_acceso(['admin', 'owner']);

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_options'])) {
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        guardar_opcion('recuerdame', limpiar_entrada($_POST['recuerdame']));
        guardar_opcion('registro', isset($_POST['registro']) ? '1' : '0');
        guardar_opcion('mailer_host', limpiar_entrada($_POST['mailer_host']));
        guardar_opcion('mailer_username', limpiar_entrada($_POST['mailer_username']));
        guardar_opcion('mailer_password', $_POST['mailer_password']); 
        guardar_opcion('mailer_port', limpiar_entrada($_POST['mailer_port']));
        
        $OPC = get_all_opciones();
        $mensaje = "✅ Configuración guardada en el núcleo.";
    }
}

$page_config = [
    'titulo' => 'Configuración',
    'menu_id' => 'sitio-web'
];

include 'sec-header.php';
?>
<main class="animated fadeIn">
    <header style="margin-bottom: 40px;">
        <h1><i class="ti-settings"></i> Configuración del Sistema</h1>
        <p style="color:var(--texto-suave)">Gestión de parámetros globales de CMS BASE.</p>
    </header>

    <?php if($mensaje): ?>
        <div class="alerta alerta-exito animated"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <div class="form-card animated">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <label>Días de persistencia de sesión</label>
            <input type="number" name="recuerdame" class="campo" value="<?php echo $OPC['recuerdame'] ?? '30'; ?>">
            
            <label style="margin-bottom:30px; cursor:pointer; display:flex; align-items:center;">
                <input type="checkbox" name="registro" <?php echo (($OPC['registro'] ?? '0') == '1') ? 'checked' : ''; ?> style="margin-right:10px; width:20px; height:20px;">
                ¿Permitir nuevos registros en el frontend?
            </label>

            <h3 style="border-top: 1px solid var(--borde); padding-top:20px;">Servidor SMTP (Envío de correos)</h3>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Host SMTP</label>
                    <input type="text" name="mailer_host" class="campo" value="<?php echo $OPC['mailer_host'] ?? ''; ?>">
                </div>
                <div>
                    <label>Puerto</label>
                    <input type="text" name="mailer_port" class="campo" value="<?php echo $OPC['mailer_port'] ?? '465'; ?>">
                </div>
            </div>

            <label>Usuario / Email</label>
            <input type="text" name="mailer_username" class="campo" value="<?php echo $OPC['mailer_username'] ?? ''; ?>">
            
            <label>Contraseña</label>
            <input type="password" name="mailer_password" class="campo" value="<?php echo $OPC['mailer_password'] ?? ''; ?>">

            <button type="submit" name="save_options" class="boton-principal">
                <i class="ti-save"></i> ACTUALIZAR NÚCLEO
            </button>
        </form>
    </div>
</main>
<?php include 'sec-footer.php'; ?>