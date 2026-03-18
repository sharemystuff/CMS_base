<?php
/* admin/admin.php */
include_once __DIR__ . '/../api/main.php';

if (!sesion_activa()) {
    header("Location: " . url_base() . "/public/login.php");
    exit; 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - CMS BASE</title>    
    <link rel="stylesheet" href="css/themify-icons.css">
    <link rel="stylesheet" href="css/backend.css">
</head>
<body style="display:flex;">
    
    <aside style="width:260px; background:#181818; min-height:100vh; padding:30px; border-right:1px solid var(--borde);">
        <div class="brand" style="margin-bottom:40px;">
            <img src="img/iconos/logo.svg" width="40">
            <h3 style="display:inline; margin-left:10px; vertical-align:middle;">CMS BASE</h3>
        </div>
        <nav>
            <a href="admin.php" style="color:var(--secundario); text-decoration:none; display:block; margin-bottom:20px;"><i class="ti-panel"></i> Dashboard</a>
            <a href="opciones.php" style="color:#fff; text-decoration:none; display:block;"><i class="ti-settings"></i> Configuración</a>
        </nav>
    </aside>

    <main style="flex:1; padding:40px;">
        <h1>Bienvenido, <?php echo e($_SESSION['user_nombre']); ?></h1>
        <div class="form-card" style="max-width:100%;">
            <h3>Estado del Sistema: <span style="color:var(--secundario)">LIVE</span></h3>
            <p>Estás usando el backend unificado de Pelín & Gemini.</p>
        </div>
    </main>

</body>
</html>