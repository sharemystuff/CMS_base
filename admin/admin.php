<?php
/* admin/admin.php */
include_once __DIR__ . '/../api/main.php';
restringir_acceso(['admin', 'owner']);

// Definición de la página para el Header
$page_config = [
    'titulo' => 'Panel de Control',
    'menu_id' => 'dashboard'
];

include 'sec-header.php'; 
?>
<main class="animated fadeIn">
    <h1>Hola, <?php echo e($_SESSION['user_nombre']); ?></h1>
    <div class="form-card">
        <h3>Resumen del Sistema</h3>
        <p>Bienvenido al motor de tu web. Desde aquí controlas todo.</p>
    </div>
</main>
<?php include 'sec-footer.php'; ?>