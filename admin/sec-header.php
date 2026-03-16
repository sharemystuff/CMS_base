<?php
/* admin/sec-header.php */
/**
 * CMS BASE - Fragmento de Cabecera Visual
 */
?>
<header class="main-navbar" style="background: #000; padding: 10px 20px; border-bottom: 1px solid #333;">
    <div class="nav-container" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="logo">
            <span style="color: #1db954; font-weight: bold; font-size: 1.2rem;">CMS BASE</span>
        </div>
        <div class="user-info">
            <span style="font-size: 0.8rem; color: #aaa;">Conectado como: <strong><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></strong></span>
        </div>
    </div>
</header>