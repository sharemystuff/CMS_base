<?php
/* tovi/funciones.php */

/**
 * CMS BASE - FUNCIONES NÚCLEO (TOVI)
 * Prioridad: Integridad Funcional y Seguridad
 */

function url_base() {
    global $OPC;
    if (!empty($OPC['url_sitio'])) return rtrim($OPC['url_sitio'], '/');
    
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $dir = str_replace('/tovi', '', dirname($script));
    $dir = str_replace('/public', '', $dir);
    
    $url = $protocol . "://" . $host . $dir;
    return rtrim($url, '/');
}

function asset($ruta) {
    $full_path = __DIR__ . '/../' . ltrim($ruta, '/');
    $version = file_exists($full_path) ? filemtime($full_path) : '1.0';
    return url_base() . '/' . ltrim($ruta, '/') . '?v=' . $version;
}

// --- GESTIÓN DE BASE DE DATOS ---

function guardar_opcion($clave, $valor) {
    global $conexion;
    if (!$conexion) return false;
    $stmt = $conexion->prepare("INSERT INTO opciones (opcion_key, opcion_dato) VALUES (?, ?) ON DUPLICATE KEY UPDATE opcion_dato = VALUES(opcion_dato)");
    $stmt->bind_param("ss", $clave, $valor);
    return $stmt->execute();
}

function leer_opcion($clave) {
    global $conexion;
    if (!$conexion) return null;
    $stmt = $conexion->prepare("SELECT opcion_dato FROM opciones WHERE opcion_key = ? LIMIT 1");
    $stmt->bind_param("s", $clave);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row['opcion_dato'];
    return null;
}

// --- GESTIÓN DE USUARIOS ---

function usuario_existe($email) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function crear_usuario_admin($nombre, $nickname, $email, $rol, $password) {
    global $conexion; 
    $pass_segura = password_hash($password, PASSWORD_BCRYPT);
    $fecha = date("Y-m-d H:i:s");
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_segura);
    return $stmt->execute();
}

function crear_usuario_temporal($nombre, $nickname, $email, $rol, $password) {
    global $conexion; 
    $pass_segura = password_hash($password, PASSWORD_BCRYPT);
    $fecha = date("Y-m-d H:i:s");
    $token = bin2hex(random_bytes(32));
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password, activo, token_verificacion) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
    $stmt->bind_param("sssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_segura, $token);
    if ($stmt->execute()) return $token;
    return false;
}

// --- EL INSTALADOR (PACHECO) ---

function pacheco_instalar($datos_db) {
    $conn = @new mysqli($datos_db['host'], $datos_db['user'], $datos_db['pass']);
    if ($conn->connect_error) return false;

    $db_name = $conn->real_escape_string($datos_db['name']);
    $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($db_name);

    $sql = "
    CREATE TABLE IF NOT EXISTS `opciones` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `opcion_key` varchar(100) NOT NULL,
      `opcion_dato` longtext DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `opcion_key` (`opcion_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `usuarios` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `nombre` varchar(100) DEFAULT NULL,
      `nickname` varchar(50) DEFAULT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `rol` varchar(20) DEFAULT 'user',
      `fecha` datetime DEFAULT NULL,
      `activo` tinyint(1) DEFAULT 0,
      `token_verificacion` varchar(100) DEFAULT NULL,
      `session_token` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `login_intentos` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(45) NOT NULL,
      `email` varchar(100) NOT NULL,
      `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if ($conn->multi_query($sql)) {
        do { if ($result = $conn->store_result()) $result->free(); } while ($conn->next_result());
    }

    $config_content = "<?php\n\$DB_DATOS = [\n    'host' => '{$datos_db['host']}',\n    'user' => '{$datos_db['user']}',\n    'pass' => '{$datos_db['pass']}',\n    'name' => '{$datos_db['name']}'\n];\n";
    return file_put_contents(__DIR__ . '/../api/config.php', $config_content);
}