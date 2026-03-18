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
    <title><?php echo $OPC['titulo_sitio'] ?? 'CMS BASE'; ?></title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
</head>
<body>

<header class="hero-front">
    <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="80" style="margin-bottom:30px;">
    <h1>CMS BASE v3</h1>
    <p>La potencia del código puro. Sin dependencias. Sin límites.</p>
    
    <div style="margin-top:40px; display:flex; gap:20px;">
        <?php if(sesion_activa()): ?>
            <a href="../admin/admin.php" class="f-boton" style="text-decoration:none; padding:15px 40px;">ENTRAR AL PANEL</a>
        <?php else: ?>
            <a href="login.php" style="color:#fff; text-decoration:none; font-weight:700; padding:15px;">Login</a>
            <a href="registro.php" class="f-boton" style="text-decoration:none; padding:15px 40px;">EMPEZAR AHORA</a>
        <?php endif; ?>
    </div>
</header>

<section style="padding:100px 20px; text-align:center; background:#fff;">
    <h2 style="font-size:2.5rem; margin-bottom:50px; color:#7A006C;">¿Por qué CMS BASE?</h2>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:40px; max-width:1200px; margin:auto;">
        <div>
            <span style="font-size:3rem;">⚡</span>
            <h3>Velocidad Real</h3>
            <p>PHP puro y CSS nativo. Cargas instantáneas.</p>
        </div>
        <div>
            <span style="font-size:3rem;">🛡️</span>
            <h3>Seguridad Nativa</h3>
            <p>Protección CSRF y saneamiento de datos integrado.</p>
        </div>
        <div>
            <span style="font-size:3rem;">💎</span>
            <h3>Modo Noche</h3>
            <p>Backend adaptativo para desarrolladores nocturnos.</p>
        </div>
    </div>
</section>

<footer style="padding:50px; text-align:center; opacity:0.5; font-size:0.9rem;">
    CMS BASE &copy; <?php echo date('Y'); ?> - Desarrollado por Pelín & Gemini
</footer>

</body>
</html>