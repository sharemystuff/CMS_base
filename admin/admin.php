<?php
/* admin/admin.php */
include_once '../api/main.php';

if (!checking()) {
    header("Location: ../public/login.php?error=acceso_denegado");
    exit; 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CMS BASE</title>    
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <style>
        body { font-family: sans-serif; background: #121212; color: #eee; }
        .card { background: #181818; padding: 20px; border-radius: 8px; border: 1px solid #333; }
    </style>
</head>
<body>
    <div style="display: flex;">        
        <aside style="width:200px; padding:20px; border-right:1px solid #333; height:100vh;">
            <h3>CMS BASE</h3>
            <a href="../index.php" style="color:white; display:block; margin-bottom:10px;">Inicio</a>
            <a href="logout.php" style="color:#ff5555;">Cerrar Sesión</a>
        </aside>        
        
        <main style="padding: 40px; flex-grow: 1;">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></h1>
            <div class="card">
                <h3>Estado de Seguridad</h3>
                <p>Sesión protegida con Token CSRF: <span style="color:#1db954;">Activo</span></p>
                <p>Rol: <strong><?php echo htmlspecialchars($_SESSION['user_rol']); ?></strong></p>
            </div>
        </main>
    </div>
</body>
</html>