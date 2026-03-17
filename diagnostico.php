<?php
/* diagnostico.php - Herramienta de Auditoría CMS BASE */
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO DE SISTEMA CMS BASE ===\n";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Sistema Operativo: " . PHP_OS . "\n";
echo "---------------------------------------\n\n";

// 1. VERIFICACIÓN DE RUTAS ABSOLUTAS
$raiz = __DIR__;
echo "Raíz detectada: $raiz\n";

$rutas_a_probar = [
    'Main Core' => $raiz . '/api/main.php',
    'Config DB' => $raiz . '/api/config.php',
    'Seguridad' => $raiz . '/seguridad/funciones.php',
    'Tovi Core' => $raiz . '/tovi/funciones.php',
    'Estilos'   => $raiz . '/assets/css/estilos.css'
];

echo "\n--- VERIFICACIÓN DE ARCHIVOS ---\n";
foreach ($rutas_a_probar as $nombre => $path) {
    if (file_exists($path)) {
        echo "[OK] $nombre: Encontrado en " . realpath($path) . "\n";
    } else {
        echo "[ERROR] $nombre: NO SE ENCUENTRA en $path\n";
    }
}

// 2. PRUEBA DE CARGA DE NÚCLEO
echo "\n--- INTENTANDO CARGAR API/MAIN.PHP ---\n";
@include_once $raiz . '/api/main.php';

if (isset($conexion)) {
    echo "[OK] Conexión a DB detectada.\n";
    echo "Estado del CMS (Variable OPC): " . (isset($OPC['estado']) ? $OPC['estado'] : 'No definido') . "\n";
} else {
    echo "[AVISO] La variable \$conexion no está definida tras cargar main.php\n";
}

// 3. PRUEBA DE FUNCIONES CRÍTICAS
echo "\n--- VERIFICACIÓN DE FUNCIONES EN MEMORIA ---\n";
$funciones_esperadas = ['url_base', 'validarCSRF', 'limpiar_entrada', 'checking', 'login'];

foreach ($funciones_esperadas as $f) {
    if (function_exists($f)) {
        echo "[DISPONIBLE] Función: $f()\n";
    } else {
        echo "[FALTANTE] Función: $f() <--- !!! CULPABLE DETECTADO\n";
    }
}

// 4. SESIÓN Y TOKEN
echo "\n--- ESTADO DE SESIÓN ---\n";
if (session_id()) {
    echo "Sesión ID: " . session_id() . "\n";
    echo "CSRF Token en Session: " . (isset($_SESSION['csrf_token']) ? 'PRESENTE' : 'VACÍO') . "\n";
} else {
    echo "[ERROR] La sesión no ha sido iniciada.\n";
}

echo "\n=== FIN DEL REPORTE ===\n";
?>