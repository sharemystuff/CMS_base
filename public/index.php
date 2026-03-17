<?php
/* public/index.php */
include_once __DIR__ . '/../api/main.php';

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
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <title><?php echo $OPC['titulo_sitio'] ?? 'CMS BASE'; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
    <style>
        nav { background: white; height: 70px; display: flex; align-items: center; padding: 0 6%; position: fixed; width: 100%; box-sizing: border-box; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .hero { height: 90vh; background: linear-gradient(135deg, var(--primario) 0%, var(--secundario) 100%); display: flex; align-items: center; padding: 0 6%; color: white; clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%); }
        .hero-content h1 { font-size: 4rem; margin: 0; line-height: 1; letter-spacing: -2px; font-weight: 800; }
        .hero-content p { font-size: 1.4rem; opacity: 0.9; margin: 20px 0; font-weight: 300; }
        .features { padding: 100px 6%; display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; text-align: center; }
        footer { padding: 50px 6%; text-align: center; border-top: 1px solid var(--ui); margin-top: 50px; background: white; }
    </style>
</head>
<body>

<nav>
    <a href="#" class="logo-nav enlace" style="display:flex; align-items:center; gap:10px;">
        <img src="<?php echo asset('assets/images/icons/logo.svg'); ?>" width="30" alt="Logo">
        CMS BASE
    </a>
    <div style="margin-left: auto;">
        <?php if(checking()): ?>
            <a href="../admin/admin.php" class="boton" style="padding:10px 20px;">Panel</a>
        <?php else: ?>
            <a href="login.php" class="boton" style="padding:10px 20px;">Acceso Staff</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <div class="hero-content">
        <h1>Potencia tu contenido.</h1>
        <p>Un sistema de gestión robusto y elegante. CMS BASE es la navaja suiza para proyectos modernos.</p>
        <a href="#mas" class="boton boton-acento" style="width:auto; padding:15px 40px;">Descubrir más</a>
    </div>
</div>

<div class="features" id="mas">
    <div class="caja" style="margin:0;"><h3>🚀 Rendimiento</h3><p>Optimizado para tiempos de carga inferiores a 100ms.</p></div>
    <div class="caja" style="margin:0;"><h3>🛠️ Open Source</h3><p>Código transparente y modular para adaptar a tu gusto.</p></div>
    <div class="caja" style="margin:0;"><h3>✨ Estética Pro</h3><p>Interfaz centrada en la experiencia de usuario y minimalismo.</p></div>
</div>

<footer>
    <p style="font-size: 0.8rem; color: #999; letter-spacing: 1px; text-transform: uppercase;">
        Desarrollado por <span style="color:var(--secundario); font-weight:bold;">Pelín & Gemini</span><br>
        Bajo la influencia de <span style="color:var(--secundario); font-weight:bold;">Mastropiero</span>
    </p>
    <p style="font-size: 0.7rem; color: #ccc; margin-top: 20px;">&copy; 2026 CMS BASE.</p>
</footer>

</body>
</html>