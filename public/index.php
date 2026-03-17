<?php
/* public/index.php */
include_once __DIR__ . '/../api/main.php';

// Si por alguna razón entran aquí sin instalar, al instalador
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
    <title><?php echo $OPC['titulo_sitio'] ?? 'CMS BASE - Home'; ?></title>
    <style>
        :root { --primary: #1db954; --bg: #0f0f0f; --card-bg: #1a1a1a; }
        body { background-color: var(--bg); color: white; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0; }
        
        nav { background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%); height: 70px; display: flex; align-items: center; padding: 0 4%; position: fixed; width: 100%; z-index: 10; }
        .logo { color: var(--primary); font-weight: bold; font-size: 1.5rem; text-decoration: none; }
        
        .hero { height: 70vh; background: linear-gradient(to right, rgba(0,0,0,0.8), transparent), url('https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?q=80&w=2069&auto=format&fit=crop'); background-size: cover; display: flex; align-items: center; padding: 0 4%; }
        .hero-content { max-width: 600px; }
        .hero-content h1 { font-size: 3rem; margin-bottom: 10px; }
        .hero-content p { font-size: 1.2rem; color: #ccc; }
        
        .btn { padding: 10px 25px; border-radius: 4px; border: none; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 20px; }
        .btn-primary { background-color: white; color: black; transition: 0.3s; }
        .btn-primary:hover { background-color: rgba(255,255,255,0.7); }

        .container { padding: 40px 4%; }
        .section-title { font-size: 1.4rem; margin-bottom: 20px; color: #e5e5e5; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
        .movie-card { background: var(--card-bg); aspect-ratio: 16/9; border-radius: 4px; overflow: hidden; transition: transform 0.3s; cursor: pointer; position: relative; }
        .movie-card:hover { transform: scale(1.05); z-index: 2; }
        .movie-card img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>

<nav>
    <a href="#" class="logo">CMS BASE</a>
    <div style="margin-left: auto;">
        <?php if(checking()): ?>
            <a href="../admin/admin.php" style="color: white; text-decoration: none;">Ir al Panel</a>
        <?php else: ?>
            <a href="login.php" style="color: white; text-decoration: none;">Iniciar Sesión</a>
        <?php endif; ?>
    </div>
</nav>

<div class="hero">
    <div class="hero-content">
        <h1>Bienvenido a tu Streaming</h1>
        <p>Tu contenido personal, gestionado con elegancia y velocidad por CMS BASE.</p>
        <a href="login.php" class="btn btn-primary">Empezar a ver</a>
    </div>
</div>

<div class="container">
    <h2 class="section-title">Añadidos recientemente</h2>
    <div class="grid">
        <?php for($i=1; $i<=6; $i++): ?>
            <div class="movie-card">
                <img src="https://picsum.photos/400/225?random=<?php echo $i; ?>" alt="Poster">
            </div>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>