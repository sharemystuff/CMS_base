<?php
/* public/index.php */
include_once __DIR__ . '/../api/main.php';

// Si no hay configuración, mandamos a Pacheco
if (!file_exists(__DIR__ . '/../api/config.php')) {
    header("Location: ../tovi/pacheco.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $OPC['titulo_sitio'] ?? 'CMS BASE'; ?></title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body>

<nav>
    <div style="display:flex; align-items:center; gap:12px; font-weight:800; font-size:1.2rem;">
        <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="35" alt="Logo">
        CMS BASE
    </div>
    <div style="margin-left: auto;">
        <?php if(sesion_activa()): ?> <a href="../admin/admin.php" class="boton-accion" style="padding:10px 20px; margin:0;">MI PANEL</a>
        <?php else: ?>
            <a href="login.php" style="text-decoration:none; color:#333; margin-right:20px;">Ingresar</a>
            <a href="registro.php" class="boton-accion" style="padding:10px 20px; margin:0;">REGISTRARME</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <h1>Bienvenido a tu nueva experiencia Web</h1>
    <p>Gestiona tu contenido con la potencia de CMS BASE.</p>
    <a href="#mas" class="boton-accion">Descubrir más</a>
</div>

<div class="features" id="mas">
    <div class="caja-feature">
        <h3>🚀 Rendimiento</h3>
        <p>Arquitectura limpia en PHP puro para una carga ultra rápida.</p>
    </div>
    <div class="caja-feature">
        <h3>🛠️ Modular</h3>
        <p>Tu CMS, tus reglas. Sin librerías pesadas ni dependencias externas.</p>
    </div>
    <div class="caja-feature">
        <h3>✨ Seguridad</h3>
        <p>Protección nativa contra CSRF y ataques comunes.</p>
    </div>
</div>

<footer style="text-align:center; padding:40px; color:#999; font-size:0.8rem;">
    CMS BASE - Desarrollado por Pelín y Gemini
</footer>

</body>
</html>