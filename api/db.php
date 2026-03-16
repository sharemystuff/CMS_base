<?php
/* api/db.php */
$host='localhost'; $user='root'; $pass='marcelo'; $db='cms_base';
@$conexion = new mysqli($host, $user, $pass, $db);
if($conexion->connect_error){ header('Location: /tovi/pacheco.php'); exit; }
$conexion->set_charset('utf8mb4');
