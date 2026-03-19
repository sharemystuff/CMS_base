<?php
/* admin/sec-header.php */

// Configuración por defecto si la página no la define
$page_config = $page_config ?? [];
$titulo_pagina = $page_config['titulo'] ?? 'CMS BASE';
$modo = $_SESSION['user_modo'] ?? 'claro';

// Parche de integridad: Si la sesión está activa pero le falta el dato de imagen (login antiguo), lo recuperamos.
if (!isset($_SESSION['user_imagen']) && isset($_SESSION['user_id'])) {
    $datos_frescos = obtener_datos_usuario($_SESSION['user_id']);
    $_SESSION['user_imagen'] = $datos_frescos['imagen'] ?? '';
}

// Determinamos el avatar del usuario (Sesión o Default)
$avatar_usuario = !empty($_SESSION['user_imagen']) ? recurso($_SESSION['user_imagen']) : recurso('admin/img/perfil.jpg');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($titulo_pagina); ?> - Admin</title>
    <link rel="stylesheet" href="<?php echo recurso('admin/css/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo recurso('admin/css/backend.css'); ?>">
    <link rel="shortcut icon" href="<?php echo recurso('admin/img/iconos/favicon.ico'); ?>">
    
    <!-- CSS Específicos de la página -->
    <?php if(!empty($page_config['css'])): foreach($page_config['css'] as $css): ?>
        <link rel="stylesheet" href="<?php echo recurso($css); ?>">
    <?php endforeach; endif; ?>
</head>
<body class="<?php echo $modo === 'oscuro' ? 'modo-noche' : ''; ?>">

    <div class="admin-layout">
        <?php include 'sec-aside.php'; ?>
        
        <div class="content-wrapper">
            <header>
                <!-- Izquierda: Marca y Link al sitio -->
                <div class="header-left">
                    <div class="logo">CMS BASE</div>
                    <a href="<?php echo url_base(); ?>" target="_blank" class="btn-header" title="Ir al sitio web">
                        <i class="ti-world"></i> <span>Ver Sitio</span>
                    </a>
                </div>

                <!-- Derecha: Herramientas y Usuario -->
                <div class="header-right">
                    <button id="btnModo" class="btn-header" title="Cambiar Modo Día/Noche">
                        <i class="ti-shine"></i>
                    </button>
                    <div class="user-profile">
                        <img src="<?php echo $avatar_usuario; ?>" alt="Perfil" class="avatar">
                        <span class="nombre"><?php echo e($_SESSION['user_nombre']); ?></span>
                    </div>
                    <a href="logout.php" class="btn-header logout" title="Cerrar sesión">
                        <i class="ti-power-off"></i>
                    </a>
                </div>
            </header>