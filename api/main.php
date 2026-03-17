<?php
/* api/main.php */

// 1. INICIO DE SESIÓN Y SEGURIDAD BASE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CARGA DE LIBRERÍAS CORE (Antes de cualquier lógica)
// Usamos __DIR__ para que las rutas sean siempre relativas a este archivo
require_once __DIR__ . '/../tovi/funciones.php';      // Para url_base() y asset()
require_once __DIR__ . '/../seguridad/funciones.php';  // Para validarCSRF(), limpiar_entrada(), etc.

// 3. DETERMINAR ENTORNO
$script_actual = $_SERVER['PHP_SELF'];
$es_instalador = (strpos($script_actual, '/tovi/') !== false);

// 4. GESTIÓN DE LA INSTALACIÓN Y CONFIGURACIÓN
$path_config = __DIR__ . '/config.php';
$OPC = []; // Inicializamos el contenedor de opciones

if (!file_exists($path_config)) {
    if (!$es_instalador) {
        header("Location: " . url_base() . "/tovi/pacheco.php");
        exit;
    }
} else {
    require_once $path_config;
    
    // Intentamos conectar a la DB
    $conexion = @new mysqli($DB_DATOS['host'], $DB_DATOS['user'], $DB_DATOS['pass'], $DB_DATOS['name']);
    
    if ($conexion->connect_error) {
        if (!$es_instalador) {
            // Si hay config pero falla la conexión, algo anda mal
            die("Error de conexión a la base de datos. Verifica api/config.php o contacta al soporte.");
        }
    } else {
        $conexion->set_charset("utf8mb4");

        // 5. CARGA DEL MODELO (Funciones que interactúan con la DB)
        if (file_exists(__DIR__ . '/funciones_model.php')) {
            include_once __DIR__ . '/funciones_model.php';
        }

        // 6. VALIDACIÓN DE ESTADO DEL SISTEMA
        $check_table = $conexion->query("SHOW TABLES LIKE 'opciones'");
        if ($check_table && $check_table->num_rows > 0) {
            $OPC = get_all_opciones(); // get_all_opciones() debe estar en funciones_model.php
            $estado = $OPC['estado'] ?? '';
            
            // Si el sitio está en fase de instalación y el usuario intenta navegar fuera de tovi
            if ($estado === 'instalando' && !$es_instalador) {
                header("Location: " . url_base() . "/tovi/pacheco.php");
                exit;
            }
        }
    }
}

// 7. FUNCIONES AUXILIARES DE ESTADO
if (!function_exists('checking')) {
    function checking() { 
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']); 
    }
}

// 8. AUTO-LOGIN Y REFUERZO DE SESIÓN
if (isset($conexion) && !$conexion->connect_error) {
    // Si el usuario no tiene sesión activa, intentamos recuperarla vía cookies (si la función existe)
    if (!checking() && function_exists('intentar_auto_login')) {
        intentar_auto_login($conexion);
    }
}

// 9. GENERACIÓN DE TOKEN CSRF (Si no existe)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}