<?php
/* admin/admin.php */
include_once __DIR__ . '/../api/main.php';

// Verificamos sesión con redirección absoluta para evitar bugs de ruta
if (!checking()) {
    header("Location: " . url_base() . "/public/login.php?error=acceso_denegado");
    exit; 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CMS BASE</title>    
    <link rel="icon" type="image/x-icon" href="<?php echo asset('assets/images/iconos/favicon.ico'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
    <style>
        body { display: flex; min-height: 100vh; background: var(--oscuro); margin: 0; }
        
        /* Sidebar Personalizado */
        aside { 
            width: 260px; 
            padding: 30px 20px; 
            border-right: 1px solid var(--borde); 
            background: #181818;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #333;
        }
        .brand h3 { color: var(--primario); font-size: 1.2rem; margin: 0; font-weight: 800; }
        
        aside a { 
            color: #aaa; 
            display: block; 
            padding: 12px 15px; 
            text-decoration: none; 
            border-radius: 8px; 
            margin-bottom: 5px;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        aside a:hover { background: #222; color: #fff; }
        aside a.active { background: var(--primario); color: #fff; font-weight: bold; }
        aside a i { margin-right: 10px; }
        
        /* Contenido Principal */
        main { padding: 50px; flex-grow: 1; color: #eee; }
        h1 { margin-top: 0; font-size: 2.2rem; font-weight: 800; margin-bottom: 30px; }
        
        .card { 
            background: #1e1e1e; 
            padding: 30px; 
            border-radius: 15px; 
            border: 1px solid var(--borde); 
            max-width: 650px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        .card h3 { color: var(--primario); margin-top: 0; display: flex; align-items: center; gap: 10px; }
        
        .status-badge { 
            display: inline-block; 
            padding: 5px 12px; 
            background: rgba(122, 0, 108, 0.2); 
            color: var(--primario); 
            border-radius: 20px; 
            font-size: 0.75rem; 
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .logout { 
            color: #ff5555 !important; 
            margin-top: auto; 
            border-top: 1px solid #333; 
            padding-top: 20px !important; 
        }
        .logout:hover { background: rgba(255, 85, 85, 0.1) !important; }
    </style>
</head>
<body>
    
    <aside>
        <div class="brand">
            <img src="<?php echo asset('assets/images/iconos/logo.svg'); ?>" width="35" alt="Logo">
            <h3>CMS BASE</h3>
        </div>

        <nav>
            <a href="admin.php" class="active"><i class="ti-panel"></i> Dashboard</a>
            <a href="opciones.php"><i class="ti-settings"></i> Configuración</a>
            <a href="<?php echo url_base(); ?>/index.php"><i class="ti-world"></i> Ver Sitio</a>
        </nav>

        <a href="logout.php" class="logout"><i class="ti-power-off"></i> Cerrar Sesión</a>
    </aside>        
    
    <main class="animated fadeIn">
        <h1>Bienvenido, <?php echo e($_SESSION['user_nombre']); ?></h1>
        
        <div class="card">
            <h3><i class="ti-shield"></i> Estado de Seguridad</h3>
            <p style="margin: 20px 0;">Sesión protegida con Token CSRF: <span class="status-badge">Activo</span></p>
            <p>Tu Rol actual: <span style="color:var(--secundario); font-weight:bold;"><?php echo e($_SESSION['user_rol']); ?></span></p>
            <p style="color: #888; font-size: 0.9rem; margin-top: 20px; line-height: 1.6;">
                Te encuentras en el entorno de desarrollo seguro <strong><?php echo e($_SERVER['HTTP_HOST']); ?></strong>. 
                Cualquier cambio crítico en la configuración del sistema debe ser realizado desde el menú de Configuración.
            </p>
        </div>
    </main>

</body>
</html>