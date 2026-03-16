<?php
/* admin/admin.php */

/**
 * CMS BASE - Panel de Administración Principal
 * Blindado por el sistema de seguridad Mangiacaprini.
 */
session_start();

// Verificamos si el usuario tiene una sesión activa
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión, se le expulsa al login inmediatamente
    header('Location: ../public/login.php');
    exit;
}

// Verificamos privilegios (admin o owner)
if ($_SESSION['user_rol'] !== 'admin' && $_SESSION['user_rol'] !== 'owner') {
    die('Acceso restringido: No tienes permisos suficientes.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - CMS BASE</title>
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>

    <?php include_once 'sec-header.php'; ?>
    
    <div class="main-wrapper" style="display: flex;">
        
        <?php include_once 'sec-aside.php'; ?>
        
        <main class="content-area" style="padding: 20px; flex-grow: 1;">
            <h1>Bienvenido al Panel, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></h1>
            <p>Estado del sistema: <strong>Operativo</strong></p>
            <p>Has ingresado como: <em><?php echo $_SESSION['user_rol']; ?></em></p>
            
            <hr style="border: 0; border-top: 1px solid #333; margin: 20px 0;">
            
            <div class="dashboard-info">
                <p>Este es el corazón de tu CMS BASE. Desde aquí gestionaremos todo.</p>
            </div>

            <a href="logout.php" style="display: inline-block; margin-top: 30px; color: #ff5555; text-decoration: none; border: 1px solid #ff5555; padding: 10px 20px; border-radius: 5px;">
                <i class="ti-power-off"></i> Cerrar Sesión Segura
            </a>
        </main>

    </div>

    <?php include_once 'sec-footer.php'; ?>

</body>
</html>