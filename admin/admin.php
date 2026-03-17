<?php
/* admin/admin.php */
include_once '../api/main.php';

// Verificamos sesión
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
        body { font-family: 'Segoe UI', sans-serif; background: #121212; color: #eee; margin: 0; display: flex; }
        
        /* Sidebar */
        aside { 
            width: 240px; 
            padding: 20px; 
            border-right: 1px solid #333; 
            height: 100vh; 
            background: #181818;
            box-sizing: border-box;
        }
        aside h3 { color: #1db954; font-size: 1.2rem; margin-bottom: 30px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        aside a { 
            color: #bbb; 
            display: block; 
            padding: 12px; 
            text-decoration: none; 
            border-radius: 5px; 
            margin-bottom: 5px;
            transition: 0.3s;
        }
        aside a:hover { background: #282828; color: #fff; }
        aside a.active { background: #1db954; color: #000; font-weight: bold; }
        aside a i { margin-right: 10px; }
        
        /* Contenido */
        main { padding: 40px; flex-grow: 1; }
        h1 { margin-top: 0; font-size: 2rem; }
        .card { 
            background: #181818; 
            padding: 25px; 
            border-radius: 12px; 
            border: 1px solid #333; 
            max-width: 600px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .card h3 { color: #1db954; margin-top: 0; }
        .status-badge { 
            display: inline-block; 
            padding: 4px 10px; 
            background: rgba(29, 185, 84, 0.1); 
            color: #1db954; 
            border-radius: 20px; 
            font-size: 0.8rem; 
            font-weight: bold;
        }
        .logout { color: #ff5555 !important; margin-top: 20px; border-top: 1px solid #333; padding-top: 15px !important; }
    </style>
</head>
<body>
    
    <aside>
        <h3>CMS BASE</h3>
        <a href="admin.php" class="active"><i class="ti-dashboard"></i> Dashboard</a>
        <a href="opciones.php"><i class="ti-settings"></i> Configuración</a>
        <a href="../index.php"><i class="ti-world"></i> Ver Sitio</a>
        <a href="logout.php" class="logout"><i class="ti-power-off"></i> Cerrar Sesión</a>
    </aside>        
    
    <main>
        <h1>Bienvenido, <?php echo e($_SESSION['user_nombre']); ?></h1>
        
        <div class="card">
            <h3><i class="ti-shield"></i> Estado de Seguridad</h3>
            <p>Sesión protegida con Token CSRF: <span class="status-badge">Activo</span></p>
            <p>Tu Rol actual: <strong><?php echo e($_SESSION['user_rol']); ?></strong></p>
            <p style="color: #888; font-size: 0.9rem; margin-top: 15px;">
                Cualquier cambio crítico en la configuración del sistema debe ser realizado desde el menú de Configuración.
            </p>
        </div>
    </main>

</body>
</html>