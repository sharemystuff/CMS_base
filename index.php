<?php
/* index.php */

require_once __DIR__ . '/api/main.php';

// 1. Verificación de Instalación: Si no hay config, vamos a Pacheco
if (!file_exists(__DIR__ . '/api/config.php')) {
    header("Location: tovi/pacheco.php");
    exit;
}

// 2. Lógica de Redirección Inteligente:
// Si ya está instalado, enviamos al usuario a la zona pública.
// El navegador hará esto tan rápido que el usuario no verá nada.
header("Location: public/index.php");
exit;