<?php
/* api/db.php */

// Datos de conexión (Pacheco los sobreescribe, pero aquí están por defecto)
$host = 'localhost'; 
$user = 'root'; 
$pass = 'marcelo'; 
$db   = 'cms_base';

// Desactivamos el reporte de errores de mysqli para manejarlo nosotros
mysqli_report(MYSQLI_REPORT_OFF);

$conexion = @new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    // Si la conexión falla y no estamos ya en el instalador, redirigimos
    if (strpos($_SERVER['PHP_SELF'], 'pacheco.php') === false) {
        header('Location: /tovi/pacheco.php');
        exit;
    }
} else {
    $conexion->set_charset('utf8mb4');
}