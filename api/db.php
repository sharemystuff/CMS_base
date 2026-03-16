<?php
/* api/db.php */
$host='localhost'; $user='root'; $pass='marcelo'; $db='12345678';

mysqli_report(MYSQLI_REPORT_OFF);
@$conexion = new mysqli($host, $user, $pass, $db);
if($conexion->connect_error){
    if(strpos($_SERVER['PHP_SELF'], 'pacheco.php') === false){ header('Location: /tovi/pacheco.php'); exit; }
}
$conexion->set_charset('utf8mb4');
