<?php
/* 404.php */
@include_once __DIR__ . '/api/main.php';

// Fallback de emergencia por si el main no carga (por ejemplo, si config.php no existe aún)
if (!function_exists('recurso')) {
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
</head>
<body class="page-404">
    <div class="error-box animated fadeIn">
        <img src="<?php echo recurso('assets/images/iconos/logo.svg'); ?>" width="70" alt="Logo" style="margin-bottom: 20px; opacity: 0.3;">
        <h1>404</h1>
        <p>Parece que te has perdido en el código del sistema.</p>
        <a href="<?php echo url_base(); ?>/" class="boton-404">VOLVER AL INICIO</a>
    </div>
</body>
</html>