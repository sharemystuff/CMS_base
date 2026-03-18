<?php
/* index.php */

// Cargamos el motor principal (que ya ajustamos para detectar .mahg y HTTPS)
require_once __DIR__ . '/api/main.php';

// 1. Verificación de Instalación
// Si el archivo config.php no existe, Pacheco (el instalador) toma el control.
if (!file_exists(__DIR__ . '/api/config.php')) {
    header("Location: tovi/pacheco.php");
    exit;
}

// 2. Redirección Silenciosa
// Usamos url_base() para que respete tu dominio https://www.cmsbase.mahg/
// El usuario no verá nada, será enviado directamente a la zona pública.
header("Location: " . url_base() . "/public/index.php");
exit;