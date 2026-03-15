<?php
/* tovi/pacheco.php  */

/**
 * CMS BASE - Instalador Visual "Pacheco"
 * Basado en el flujo de instalación definido por Pelín.
 */

// Evitamos que se acceda si el sistema ya está configurado
// (En el futuro, index.php definirá esta constante solo si db.php falta)
if (!defined('INSTALACION_PERMITIDA')) {
    // Por ahora, para pruebas, puedes comentar la línea de abajo
    // die("Acceso denegado: El sistema ya está instalado.");
}

$error = "";

// PROCESAMIENTO DEL FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['instalar_db'])) {
    
    $datos_db = [
        'host' => $_POST['db_host'],
        'user' => $_POST['db_user'],
        'pass' => $_POST['db_pass'],
        'name' => $_POST['db_name']
    ];

    // Intentamos la instalación usando la función lógica que creamos antes
    // Nota: Esta función debe estar disponible o incluida
    $resultado = pacheco_instalar($datos_db);

    if ($resultado) {
        // SI FUNCIONA: Escribimos el archivo api/db.php (Siguiente paso lógico)
        // Por ahora, solo mostramos éxito para probar la conexión
        echo "<div style='background:green; color:white; padding:20px;'>✅ Conexión exitosa y tablas creadas.</div>";
    } else {
        $error = "❌ No se pudo conectar a la base de datos. Revisa los datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalador Pacheco - CMS BASE</title>
    <style>
        body { font-family: sans-serif; background: #1a1a1a; color: #eee; display: flex; justify-content: center; padding-top: 50px; }
        .card { background: #252525; padding: 30px; border-radius: 8px; width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        h1 { color: #1db954; font-size: 1.5rem; text-align: center; }
        label { display: block; margin-top: 15px; font-size: 0.9rem; color: #888; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #333; background: #000; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; margin-top: 25px; background: #1db954; border: none; color: #000; font-weight: bold; cursor: pointer; border-radius: 4px; }
        .error { color: #ff5555; font-size: 0.8rem; margin-top: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <h1>CMS BASE - Instalación</h1>
        <p style="font-size: 0.8rem; text-align: center;">Configuración de Base de Datos</p>

        <?php if($error): ?> <div class="error"><?php echo $error; ?></div> <?php endif; ?>

        <form method="POST">
            <label>Servidor (Host)</label>
            <input type="text" name="db_host" value="localhost" required>

            <label>Usuario MySQL</label>
            <input type="text" name="db_user" placeholder="ej: root" required>

            <label>Contraseña MySQL</label>
            <input type="password" name="db_pass" placeholder="Tu contraseña">

            <label>Nombre de la Base de Datos</label>
            <input type="text" name="db_name" placeholder="ej: cms_base" required>

            <button type="submit" name="instalar_db">CONFIGURAR Y CREAR TABLAS</button>
        </form>
    </div>
</body>
</html>