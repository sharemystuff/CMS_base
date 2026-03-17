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
</head>
<body>

<nav>
    <a href="#" class="logo-nav enlace" style="display:flex; align-items:center; gap:12px; font-weight: 800; letter-spacing: -1px; font-size: 1.2rem;">
        <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="35" alt="Logo">
        CMS BASE
    </a>
    <div style="margin-left: auto;">
        <?php if(checking()): ?>
            <a href="../admin/admin.php" class="boton boton-nav">MI PANEL</a>
        <?php else: ?>
            <a href="login.php" class="boton-nav enlace" style="color: var(--primario); margin-right: 20px;">Acceso Staff</a>
            <a href="registro.php" class="boton boton-nav">REGISTRARME</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <div class="hero-content">
        <h1>Potencia tu <br><span style="color: rgba(255,255,255,0.7)">contenido.</span></h1>
        <p>Un sistema de gestión robusto, minimalista y absurdamente rápido. La navaja suiza que Pelín diseñó para tus proyectos más ambiciosos.</p>
        <div style="display: flex; gap: 20px;">
            <a href="#mas" class="boton" style="background: white; color: var(--primario); width: auto; padding: 18px 40px; border-radius: 50px;">EMPEZAR AHORA</a>
            <a href="#" class="enlace" style="color: white; align-self: center; font-weight: 600;">Ver documentación →</a>
        </div>
    </div>
</div>

<div class="features" id="mas">
    <div class="caja-feature">
        <div style="font-size: 2.5rem; margin-bottom: 15px;">🚀</div>
        <h3>Rendimiento</h3>
        <p>Arquitectura limpia sin librerías pesadas. Velocidad de carga que enamora a Google.</p>
    </div>
    <div class="caja-feature">
        <div style="font-size: 2.5rem; margin-bottom: 15px;">🛠️</div>
        <h3>Estructura Pro</h3>
        <p>Código modular fácil de entender y extender. Tu CMS, tus reglas, sin dependencias.</p>
    </div>
    <div class="caja-feature">
        <div style="font-size: 2.5rem; margin-bottom: 15px;">✨</div>
        <h3>Experiencia</h3>
        <p>Interfaz intuitiva pensada para que te centres en lo que importa: crear contenido único.</p>
    </div>
</div>

<footer>
    <div style="margin-bottom: 40px;">
        <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="40" alt="Logo" style="opacity: 0.5;">
    </div>
    <p style="font-size: 0.8rem; color: #999; letter-spacing: 2px; text-transform: uppercase; line-height: 2;">
        Engineered by <span style="color:var(--secundario); font-weight:bold;">Pelín & Gemini</span><br>
        <small style="opacity: 0.6;">"La música es la aritmética de los sonidos..." — Mastropiero</small>
    </p>
    <p style="font-size: 0.7rem; color: #ccc; margin-top: 40px;">&copy; 2026 CMS BASE. Todos los derechos reservados.</p>
</footer>

</body>
</html>