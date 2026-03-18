<?php
/* index.php */

require_once __DIR__ . '/api/main.php';

// Si no hay configuración, Pacheco toma el control
if (!file_exists(__DIR__ . '/api/config.php')) {
    header("Location: tovi/pacheco.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $OPC['titulo_sitio'] ?? 'CMS BASE'; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
    <style>
        /* Estilos específicos de la splash screen que usan tus variables de estilos.css */
        body { 
            background: var(--oscuro); 
            color: var(--primario); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .loader { 
            text-align: center; 
            border: 1px solid var(--borde); 
            padding: 40px; 
            border-radius: 12px; 
            background: #1e1e1e; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .dots { font-size: 24px; color: var(--secundario); }
        h1 { margin-bottom: 10px; letter-spacing: 2px; }
    </style>
</head>
<body>
    <div class="loader">
        <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="80" alt="Logo" style="margin-bottom:20px;">
        <h1>CMS BASE</h1>
        <p>Iniciando sistema<span class="dots">...</span></p>
    </div>
    <script>
        setTimeout(() => {
            window.location.href = "public/index.php";
        }, 1500);
    </script>
</body>
</html>