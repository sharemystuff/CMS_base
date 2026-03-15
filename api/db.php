<?php
/* api/db.php */
// Archivo generado automáticamente por Pacheco
$host = 'localhost';
$user = 'root';
$pass = 'marcelo';
$db   = 'cms_base';

$conexion = @new mysqli($host, $user, $pass, $db);
if ($conexion->connect_error) { die('Error de Conexión: ' . $conexion->connect_error); }
$conexion->set_charset('utf8mb4');
