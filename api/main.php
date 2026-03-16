<?php
/* api/main.php */

/**
 * CMS BASE - El "Cerebro" Central
 */

$db_file = __DIR__ . '/db.php';

// 1. SENSOR DE INSTALACIÓN
if (!file_exists($db_file)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    header("Location: $protocol://$host/tovi/pacheco.php");
    exit;
}

include_once __DIR__ . '/db.php';
include_once __DIR__ . '/../seguridad/funciones.php';
include_once __DIR__ . '/../tovi/funciones.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 2. CARGA DE SALTS
$res_salt = $conexion->query("SELECT opcion_dato FROM opciones WHERE opcion_key = 'salt_key'");
if ($res_salt && $res_salt->num_rows > 0) {
    define('AUTH_SALT', $res_salt->fetch_assoc()['opcion_dato']);
} else {
    define('AUTH_SALT', 'base_fallback_key_88');
}

// 3. INTENTO DE AUTO-LOGIN (Persistencia)
// Si no hay sesión, intentamos usar la cookie
intentar_auto_login($conexion);

/**
 * Función checking(): Protege páginas privadas
 */
function checking() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /public/login.php");
        exit;
    }
}