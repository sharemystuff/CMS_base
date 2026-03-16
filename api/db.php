<?php
/* api/db.php - Generado por Pacheco Installer */

$host = 'localhost';
$user = 'root';
$pass = 'marcelo';
$db   = 'cms_base';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conexion = new mysqli($host, $user, $pass, $db);
    $conexion->set_charset('utf8mb4');
} catch (Exception $e) {
    if (strpos($_SERVER['PHP_SELF'], 'pacheco.php') === false) {
        header('Location: /tovi/pacheco.php');
        exit;
    }
}
