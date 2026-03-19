<?php
/* admin/sec-header.php */
/**
 * CMS BASE - Fragmento de Cabecera Visual
 */
?>
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
            <img src="<?php echo recurso('admin/img/perfil.jpg'); ?>" alt="Perfil" class="avatar">
            <span class="nombre"><?php echo e($_SESSION['user_nombre']); ?></span>
        </div>
        <a href="logout.php" class="btn-header logout" title="Cerrar sesión">
            <i class="ti-power-off"></i>
        </a>
    </div>
</header>