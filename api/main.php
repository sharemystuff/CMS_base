<?php
/* api/main.php */

// 1. SENSOR DE INSTALACIÓN
if (!file_exists(__DIR__ . '/db.php')) {
    header("Location: /tovi/pacheco.php");
    exit;
}

include_once __DIR__ . '/db.php';
include_once __DIR__ . '/../seguridad/funciones.php';
include_once __DIR__ . '/../tovi/funciones.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. CARGA DE SALTS DESDE LA DB
// Obtenemos la llave única para reforzar cookies/sesiones
$res_salt = $conexion->query("SELECT opcion_dato FROM opciones WHERE opcion_key = 'salt_key'");
define('AUTH_SALT', ($res_salt->num_rows > 0) ? $res_salt->fetch_assoc()['opcion_dato'] : 'default_salt_fallback');

/**
 * Función checking(): Protege el acceso al admin
 */
function checking() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /public/login.php");
        exit;
    }
}