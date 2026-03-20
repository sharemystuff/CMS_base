<?php
/* admin/opciones.php */
include_once __DIR__ . '/../api/main.php';

restringir_acceso(['admin', 'owner']);

// Obtener datos frescos
$OPC = obtener_todas_las_opciones();
$seo_img = !empty($OPC['op_imagen']) ? recurso($OPC['op_imagen']) : recurso('assets/images/opengraph.jpg');

$page_config = [
    'titulo' => 'Configuración',
    'menu_id' => 'sitio-web',
    'css' => ['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css'],
    'scripts' => ['https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js']
];

include 'sec-header.php';
?>
<main class="animated fadeIn">
    
    <!-- Navegación de Pestañas -->
    <div class="tabs-nav">
        <div class="tab-link active" data-tab="tab-seo">SEO y Opengraph</div>
        <div class="tab-link" data-tab="tab-contacto">Contacto</div>
        <?php if($_SESSION['user_rol'] === 'admin'): ?>
        <div class="tab-link" data-tab="tab-avanzadas"><i class="ti-settings"></i> Avanzadas</div>
        <?php endif; ?>
    </div>

    <!-- TAB 1: SEO -->
    <div id="tab-seo" class="tab-content active">
        <div class="form-card">
            <h3>Configuración SEO Global</h3>
            <p style="color:var(--texto-suave); font-size:0.9rem; margin-bottom:20px;">Estos datos se mostrarán cuando compartas tu sitio en redes sociales.</p>
            
            <!-- Imagen SEO -->
            <label>Imagen para Redes Sociales (16:9)</label>
            <div class="seo-image-wrapper" title="Cambiar imagen SEO">
                <img src="<?php echo $seo_img; ?>" alt="SEO Image" id="imgSeoActual">
                <div class="perfil-overlay">
                    <i class="ti-camera" style="font-size: 2rem; margin-bottom: 5px;"></i>
                    <span>Reemplazar (Mín. 1280x720)</span>
                </div>
            </div>
            <input type="file" id="inputSeo" accept="image/png, image/jpeg, image/jpg" style="display:none;">

            <form id="formSeo">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="guardar_seo">

                <label>Nombre del Sitio</label>
                <input type="text" name="nombre_sitio" class="campo" value="<?php echo e($OPC['nombre_sitio'] ?? 'CMS BASE'); ?>" placeholder="Ej: Mi Gran Proyecto">

                <label>Descripción Corta</label>
                <textarea name="descripcion_sitio" class="campo" rows="3" placeholder="Descripción que aparecerá en Google..."><?php echo e($OPC['descripcion_sitio'] ?? ''); ?></textarea>

                <button type="submit" class="boton-p"><i class="ti-save"></i> GUARDAR SEO</button>
            </form>
        </div>
    </div>

    <!-- TAB 2: Contacto -->
    <div id="tab-contacto" class="tab-content">
        <div class="form-card">
            <h3>Información de Contacto</h3>
            <p>Próximamente...</p>
        </div>
    </div>

    <!-- TAB 3: Avanzadas -->
    <?php if($_SESSION['user_rol'] === 'admin'): ?>
    <div id="tab-avanzadas" class="tab-content">
        <div class="form-card" style="border-left: 4px solid #e74c3c;">
            <h3><i class="ti-alert"></i> Opciones del Núcleo</h3>
            <form id="formAvanzadas">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="guardar_opciones_avanzadas">
                
                <label>Días de persistencia de sesión</label>
                <input type="number" name="recuerdame" class="campo" value="<?php echo $OPC['recuerdame'] ?? '30'; ?>">
                
                <label style="margin-bottom:30px; cursor:pointer; display:flex; align-items:center;">
                    <input type="checkbox" name="registro" <?php echo (($OPC['registro'] ?? '0') == '1') ? 'checked' : ''; ?> style="margin-right:10px; width:20px; height:20px;">
                    ¿Permitir nuevos registros en el frontend?
                </label>

                <h3 style="border-top: 1px solid var(--borde); padding-top:20px; margin-top:20px;">Servidor SMTP (Envío de correos)</h3>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label>Host SMTP</label>
                        <input type="text" name="mailer_host" class="campo" value="<?php echo e($OPC['mailer_host'] ?? ''); ?>" placeholder="ej: smtp.gmail.com">
                    </div>
                    <div>
                        <label>Puerto</label>
                        <input type="text" name="mailer_port" class="campo" value="<?php echo e($OPC['mailer_port'] ?? '465'); ?>">
                    </div>
                </div>

                <label>Usuario / Email</label>
                <input type="text" name="mailer_username" class="campo" value="<?php echo e($OPC['mailer_username'] ?? ''); ?>">
                
                <label>Contraseña SMTP</label>
                <div class="input-pass-wrapper">
                    <input type="password" name="mailer_password" class="campo" value="<?php echo e($OPC['mailer_password'] ?? ''); ?>">
                    <button type="button" class="btn-eye" tabindex="-1"><i class="ti-eye"></i></button>
                </div>

                <button type="submit" class="boton-p"><i class="ti-save"></i> GUARDAR AVANZADAS</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- Modal Cropper SEO (Oculto) -->
<div id="modalCropperSeo" class="modal-overlay">
    <div class="modal-content">
        <h3 style="color:var(--texto)">Ajustar Imagen SEO (16:9)</h3>
        <div class="cropper-container-wrapper">
            <img id="imageToCropSeo" src="">
        </div>
        <div class="modal-actions">
            <button type="button" id="btnCancelarCropSeo" class="btn-cancelar"><i class="ti-close"></i> Cancelar</button>
            <button type="button" id="btnAceptarCropSeo" class="btn-aceptar"><i class="ti-check"></i> Recortar y Guardar</button>
        </div>
    </div>
</div>

<?php include 'sec-footer.php'; ?>