<?php
/* public/index.php */
include_once __DIR__ . '/../api/main.php';

// Redirección si no está instalado
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
    <link rel="icon" type="image/x-icon" href="/assets/images/iconos/favicon.ico">
    <title><?php echo $OPC['titulo_sitio'] ?? 'CMS BASE - El Futuro es Abierto'; ?></title>
    <style>
        :root { 
            --primary: #7A006C; 
            --secondary: #9C1A8E;
            --accent: #F2C94C;
            --bg: #F5F6F8;
            --ui: #D9DCE1;
            --text: #222222;
        }
        
        body { background-color: var(--bg); color: var(--text); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        
        nav { background: white; height: 70px; display: flex; align-items: center; padding: 0 6%; position: fixed; width: 100%; box-sizing: border-box; z-index: 100; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .logo-nav { display: flex; align-items: center; text-decoration: none; color: var(--primary); font-weight: 800; font-size: 1.2rem; gap: 10px; }
        
        .hero { 
            height: 90vh; 
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); 
            display: flex; 
            align-items: center; 
            padding: 0 6%; 
            color: white;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }
        .hero-content { max-width: 700px; }
        .hero-content h1 { font-size: 4rem; margin: 0; line-height: 1; letter-spacing: -2px; }
        .hero-content p { font-size: 1.4rem; opacity: 0.9; margin: 20px 0; font-weight: 300; }
        
        .btn { padding: 14px 30px; border-radius: 50px; border: none; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; transition: 0.3s; }
        .btn-accent { background-color: var(--accent); color: var(--primary); box-shadow: 0 4px 15px rgba(242, 201, 76, 0.4); }
        .btn-accent:hover { transform: translateY(-3px); background-color: white; }

        .features { padding: 100px 6%; display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; text-align: center; }
        .feature-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); border: 1px solid var(--ui); }
        .feature-card h3 { color: var(--primary); margin-top: 0; }
        .feature-card p { font-size: 0.95rem; color: #666; line-height: 1.6; }

        footer { padding: 50px 6%; text-align: center; border-top: 1px solid var(--ui); margin-top: 50px; }
        .credits { font-size: 0.8rem; color: #999; letter-spacing: 1px; text-transform: uppercase; }
        .credits span { color: var(--secondary); font-weight: bold; }
    </style>
</head>
<body>

<nav>
    <a href="#" class="logo-nav">
        <img src="/assets/images/icons/logo.svg" width="30" alt="Logo">
        CMS BASE
    </a>
    <div style="margin-left: auto;">
        <?php if(checking()): ?>
            <a href="../admin/admin.php" class="btn" style="color: var(--primary);">Panel de Control</a>
        <?php else: ?>
            <a href="login.php" class="btn" style="background: var(--primary); color: white;">Acceso Staff</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <div class="hero-content">
        <h1>Potencia tu contenido.</h1>
        <p>Un sistema de gestión robusto, diseñado bajo estándares de alto rendimiento y elegancia visual. CMS BASE es la navaja suiza para proyectos modernos.</p>
        <a href="#mas" class="btn btn-accent">Descubre las virtudes</a>
    </div>
</div>

<div class="features" id="mas">
    <div class="feature-card">
        <h3>🚀 Rendimiento</h3>
        <p>Arquitectura ligera optimizada para tiempos de carga inferiores a 100ms. Sin bloqueos, sin demoras.</p>
    </div>
    <div class="feature-card">
        <h3>🛠️ Open Source</h3>
        <p>Código transparente y modular. La libertad de adaptar cada línea a tus necesidades específicas.</p>
    </div>
    <div class="feature-card">
        <h3>✨ Estética Pro</h3>
        <p>Interfaz inspirada en plataformas de élite, centrada en la experiencia de usuario y el minimalismo.</p>
    </div>
</div>

<footer>
    <p class="credits">
        Desarrollado con maestría por <span>Pelín & Gemini</span><br>
        Bajo la inefable influencia de <span>Johann Sebastian Mastropiero</span>
    </p>
    <p style="font-size: 0.7rem; color: #ccc; margin-top: 20px;">&copy; 2026 CMS BASE - Todos los derechos reservados.</p>
</footer>

</body>
</html>