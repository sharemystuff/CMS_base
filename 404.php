<?php
/* 404.php */
@include_once __DIR__ . '/api/main.php';

// Fallback de emergencia por si el main no carga (por ejemplo, si config.php no existe aún)
if (!function_exists('asset')) {
    function recurso($path) { return '/' . ltrim($path, '/'); }
}
if (!function_exists('url_base')) {
    function url_base() { return ''; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 - No Encontrado</title>
    <link rel="icon" type="image/x-icon" href="<?php echo recurso('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo recurso('assets/css/estilos.css'); ?>">
    <style>
        body { 
            background: var(--oscuro); 
            color: #fff; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            text-align: center; 
        }
        .error-box { max-width: 400px; padding: 20px; }
        h1 { color: var(--primario); font-size: 8rem; margin: 0; font-weight: 800; line-height: 1; }
        p { color: var(--texto-suave); font-size: 1.2rem; margin: 20px 0 40px 0; }
        .boton-404 {
            background: var(--primario);
            color: #fff;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            transition: 0.3s;
            display: inline-block;
        }
        .boton-404:hover {
            transform: scale(1.05);
            background: var(--secundario);
            color: var(--oscuro);
        }
    </style>
</head>
<body>
    <div class="error-box animated fadeIn">
        <img src="<?php echo recurso('assets/images/iconos/logo.svg'); ?>" width="70" alt="Logo" style="margin-bottom: 20px; opacity: 0.3;">
        <h1>404</h1>
        <p>Parece que te has perdido en el código del sistema.</p>
        <a href="<?php echo url_base(); ?>/" class="boton-404">VOLVER AL INICIO</a>
    </div>
</body>
</html>