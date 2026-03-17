<?php
/* index.php */

require_once __DIR__ . '/api/main.php';

// Si no hay configuración, Pacheco toma el control
if (!file_exists(__DIR__ . '/api/config.php')) {
    header("Location: tovi/pacheco.php");
    exit;
}

// Si llegamos aquí, el sistema está instalado.
// Saludamos al index y decidimos el destino.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CMS BASE - Iniciando...</title>
    <style>
        body { background: #121212; color: #1db954; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: 'Segoe UI', sans-serif; }
        .loader { text-align: center; border: 1px solid #333; padding: 20px; border-radius: 8px; background: #1e1e1e; }
        .dots { font-size: 24px; }
    </style>
</head>
<body>
    <div class="loader">
        <h1>CMS BASE</h1>
        <p>Verificando sesión<span class="dots">...</span></p>
    </div>
    <script>
        // Un pequeño saludo al log y redirección inmediata
        console.log("CMS BASE: Sistema listo. Redireccionando...");
        window.location.href = "<?php echo checking() ? 'admin/admin.php' : 'public/index.php'; ?>";
    </script>
</body>
</html>