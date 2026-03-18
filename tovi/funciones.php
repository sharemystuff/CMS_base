<?php
/* tovi/funciones.php */

/**
 * CMS BASE - FUNCIONES CORE (TOVI)
 */

function url_base() {
    global $OPC;
    
    // Si la URL está definida en la base de datos, la usamos limpiando la barra final
    if (!empty($OPC['url_sitio'])) {
        return rtrim($OPC['url_sitio'], '/');
    }
    
    // Detectamos el protocolo y host
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    // Detectamos el subdirectorio (si existe)
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $dir = str_replace('/tovi', '', dirname($script));
    $dir = str_replace('/public', '', $dir);
    
    // Construimos la URL y aplicamos rtrim para que NUNCA termine en /
    $url = $protocol . "://" . $host . $dir;
    return rtrim($url, '/');
}

function asset($ruta) {
    $full_path = __DIR__ . '/../' . ltrim($ruta, '/');
    $version = file_exists($full_path) ? filemtime($full_path) : '1.0';
    return url_base() . '/' . ltrim($ruta, '/') . '?v=' . $version;
}

function pacheco_instalar($datos_db) {
    $conn = @new mysqli($datos_db['host'], $datos_db['user'], $datos_db['pass']);
    if ($conn->connect_error) return false;
    $db_name = $conn->real_escape_string($datos_db['name']);
    $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($db_name);

    $sql = "
    CREATE TABLE IF NOT EXISTS opciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        opcion_key VARCHAR(50) UNIQUE,
        opcion_dato TEXT
    );
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        nickname VARCHAR(50),
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        rol VARCHAR(20),
        foto VARCHAR(255),
        activo TINYINT(1) DEFAULT 0,
        token_verificacion VARCHAR(100),
        session_token VARCHAR(100),
        fecha DATETIME
    );";
    
    $conn->multi_query($sql);
    while ($conn->next_result()) {;} // Limpiar resultados

    return true;
}

function get_opcion($key) {
    global $conexion;
    if (!$conexion) return false;
    $stmt = $conexion->prepare("SELECT opcion_dato FROM opciones WHERE opcion_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result();
    return ($res->num_rows > 0) ? $res->fetch_assoc()['opcion_dato'] : false;
}

function update_opcion($key, $valor) {
    global $conexion;
    $stmt = $conexion->prepare("INSERT INTO opciones (opcion_key, opcion_dato) VALUES (?, ?) ON DUPLICATE KEY UPDATE opcion_dato = VALUES(opcion_dato)");
    $stmt->bind_param("ss", $key, $valor);
    return $stmt->execute();
}

// --- FUNCIONES DE USUARIO ---

function create_user_admin($nombre, $nickname, $email, $rol, $password) {
    global $conexion; 
    // Seguridad: BCRYPT es el estándar del CMS BASE
    $pass_segura = password_hash($password, PASSWORD_BCRYPT);
    $fecha = date("Y-m-d H:i:s");
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_segura);
    return $stmt->execute();
}

function create_user_pendiente($nombre, $nickname, $email, $rol, $password) {
    global $conexion; 
    $pass_segura = password_hash($password, PASSWORD_BCRYPT);
    $fecha = date("Y-m-d H:i:s");
    $token = bin2hex(random_bytes(32));
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password, activo, token_verificacion) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
    $stmt->bind_param("sssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_segura, $token);
    return ($stmt->execute()) ? $token : false;
}

function user_existe($email) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return ($stmt->get_result()->num_rows > 0);
}