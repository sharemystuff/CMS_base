<?php
/* admin/admin.php */

/**
 * CMS BASE - Panel Admin
 * Blindado contra accesos sin instalación.
 */

// 1. SENSOR DE INSTALACIÓN FÍSICO
if (!file_exists(__DIR__ . '/../api/db.php')) {
    header("Location: ../tovi/pacheco.php");
    exit;
}

include_once __DIR__ . '/../api/db.php';

session_start();

// Verificamos sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - CMS BASE</title>
    <link rel="stylesheet" href="../assets/css/themify-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <?php include_once 'sec-header.php'; ?>
    
    <div class="main-wrapper" style="display: flex;">
        <?php include_once 'sec-aside.php'; ?>
        
        <main class="content-area" style="padding: 20px; flex-grow: 1;">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></h1>
            <p>Estado del sistema: <span style="color: #1db954;">● Conectado</span></p>
            
            <div style="margin-top: 40px; background: #181818; padding: 20px; border-radius: 8px; border: 1px solid #333;">
                <h3>Configuración Inicial Detectada</h3>
                <p>La base de datos está operativa y las opciones base han sido creadas.</p>
            </div>

            <a href="logout.php" style="display: inline-block; margin-top: 30px; color: #ff5555; text-decoration: none; border: 1px solid #ff5555; padding: 10px 20px; border-radius: 5px;">
                Cerrar Sesión
            </a>
        </main>
    </div>

    <?php include_once 'sec-footer.php'; ?>
</body>
</html>