<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conexion = new mysqli('localhost', 'root', 'marcelo', 'cms_base');
    $conexion->set_charset('utf8mb4');
} catch (Exception $e) { header('Location: /tovi/pacheco.php'); exit; }
