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

function recurso($ruta) {
    $clean_ruta = ltrim($ruta, '/');
    $full_path = __DIR__ . '/../' . $clean_ruta;
    $version = file_exists($full_path) ? filemtime($full_path) : '1.0';
    return url_base() . '/' . $clean_ruta . '?v=' . $version;
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
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password, activo, admin) VALUES (?, ?, ?, ?, ?, ?, 1, 'claro')");
    $stmt->bind_param("ssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_segura);
    return $stmt->execute();
}

function crear_meta_usuario($usu_id, $usu_key, $usu_valor) {
    global $conexion;
    // Validación de seguridad: Aseguramos que existan datos. Permitimos el 0 como valor válido.
    if (empty($usu_id) || empty($usu_key) || (empty($usu_valor) && $usu_valor !== '0' && $usu_valor !== 0)) {
        return "Error: Faltan datos obligatorios para crear el metadato.";
    }
    // Sentencia preparada para evitar inyección SQL
    $stmt = $conexion->prepare("INSERT INTO usuarios_meta (usu_id, usu_key, usu_valor) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE usu_valor = VALUES(usu_valor)");
    if (!$stmt) return "Error crítico de base de datos: " . $conexion->error;
    $stmt->bind_param("iss", $usu_id, $usu_key, $usu_valor);
    $resultado = $stmt->execute() ? $stmt->insert_id : "Error al guardar en base de datos: " . $stmt->error;
    $stmt->close();
    return $resultado;
}

function crear_usuario_temporal($nombre, $nickname, $email, $rol, $password) {
    global $conexion; 
    $pass_segura = password_hash($password, PASSWORD_BCRYPT);
    $fecha = date("Y-m-d H:i:s");
    $token = bin2hex(random_bytes(32));
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password, activo, token_verificacion, admin) VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'claro')");
    $stmt->bind_param("sssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_segura, $token);
    if ($stmt->execute()) return $token;
    return false;
}

function actualizar_preferencia_admin($usu_id, $modo) {
    global $conexion;
    // Solo permitimos valores controlados
    $modo_seguro = ($modo === 'oscuro') ? 'oscuro' : 'claro';
    $stmt = $conexion->prepare("UPDATE usuarios SET admin = ? WHERE id = ?");
    $stmt->bind_param("si", $modo_seguro, $usu_id);
    return $stmt->execute();
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
      `imagen` varchar(255) DEFAULT NULL,
      `admin` varchar(50) DEFAULT 'claro',
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

    CREATE TABLE IF NOT EXISTS `usuarios_meta` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `usu_id` int(11) NOT NULL,
      `usu_key` varchar(50) NOT NULL,
      `usu_valor` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_meta` (`usu_id`, `usu_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if ($conn->multi_query($sql)) {
        do { if ($result = $conn->store_result()) $result->free(); } while ($conn->next_result());
    }

    $config_content = "<?php\n\$DB_DATOS = " . var_export($datos_db, true) . ";\n";
    return file_put_contents(__DIR__ . '/../api/config.php', $config_content);
}