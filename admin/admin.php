<?php
/* admin/admin.php */
include_once __DIR__ . '/../api/main.php';
restringir_acceso(['admin', 'owner']);

// Detectamos preferencia de base de datos (cargada en sesión)
$modo = $_SESSION['user_modo'] ?? 'claro';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo recurso('admin/css/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo recurso('admin/css/backend.css'); ?>">
</head>
<body class="<?php echo $modo === 'oscuro' ? 'modo-noche' : ''; ?>">

    <div class="admin-layout">
        <?php include 'sec-aside.php'; ?>
        
        <div class="content-wrapper">
            <?php include 'sec-header.php'; ?>
            
            <main class="animated fadeIn">
                <h1>Hola, <?php echo e($_SESSION['user_nombre']); ?></h1>
                <div class="form-card">
                    <h3>Resumen del Sistema</h3>
                    <p>Bienvenido al motor de tu web. Desde aquí controlas todo.</p>
                </div>
            </main>

            <?php include 'sec-footer.php'; ?>
        </div>
    </div>

    <script>
        const CMS_VARS = {
            csrf_token: "<?php echo $_SESSION['csrf_token']; ?>"
        };
    </script>
    <script src="<?php echo recurso('assets/plugins/jquery.js'); ?>"></script>
    <script src="<?php echo recurso('admin/js/admin.js'); ?>"></script>
</body>
</html>