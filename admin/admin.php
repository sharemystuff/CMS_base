<?php
/* admin/admin.php */

// Cargamos el cerebro y protegemos la página
include_once '../api/main.php';
checking();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CMS BASE</title>
    
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="admin/css/admin.css">
</head>
<body class="admin-panel">

    <?php include_once 'sec-header.php'; ?>
    
    <div class="main-wrapper" style="display: flex;">
        
        <?php include_once 'sec-aside.php'; ?>
        
        <main class="content-area" style="padding: 20px; flex-grow: 1;">
            <header class="content-header">
                <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></h1>
                <p>Estás en el centro de mando de tu CMS.</p>
            </header>

            <section class="dashboard-widgets" style="margin-top: 30px;">
                <div style="background: #181818; padding: 20px; border-radius: 8px; border: 1px solid #333;">
                    <h3>Estado de Conexión</h3>
                    <p>Sesión activa para el rol: <strong><?php echo $_SESSION['user_rol']; ?></strong></p>
                    <p>Persistencia: <span style="color: #1db954;">Activada</span></p>
                </div>
            </section>

            <footer style="margin-top: 50px;">
                <a href="logout.php" style="color: #ff5555; text-decoration: none; font-size: 0.9rem;">
                    <i class="ti-power-off"></i> Cerrar Sesión Segura
                </a>
            </footer>
        </main>

    </div>

    <?php include_once 'sec-footer.php'; ?>

</body>
</html>