<?php
/* tovi/funciones.php */

/**
 * CMS BASE - FUNCIONES CORE (TOVI)
 */

function url_base() {
    global $OPC;
    if (!empty($OPC['url_sitio'])) return rtrim($OPC['url_sitio'], '/');
    
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $dir = str_replace('/tovi', '', dirname($script));
    $dir = str_replace('/public', '', $dir);
    return $protocol . "://" . $host . rtrim($dir, '/');
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
    $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($db_name);
    
    $tablas = [
        "usuarios" => "id INT AUTO_INCREMENT PRIMARY KEY, nombre TEXT, nickname TEXT, email TEXT, password TEXT, rol ENUM('admin', 'owner', 'editor'), fecha DATETIME, special_key TEXT, session_token VARCHAR(255), activo INT DEFAULT 0, token_verificacion VARCHAR(255), reset_token VARCHAR(255), reset_expira DATETIME",
        "opciones" => "id INT AUTO_INCREMENT PRIMARY KEY, opcion_id VARCHAR(255), opcion_key VARCHAR(255) UNIQUE, opcion_dato TEXT",
        "posts" => "id INT AUTO_INCREMENT PRIMARY KEY, tipo TEXT, titulo TEXT, url TEXT, contenido TEXT, opengraph TEXT, imagen TEXT, fecha DATETIME, autor INT, categorias TEXT, etiquetas TEXT",
        "user_meta" => "id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, user_key TEXT, user_dato TEXT",
        "post_meta" => "id INT AUTO_INCREMENT PRIMARY KEY, post_id INT, post_key TEXT, post_dato TEXT",
        "login_intentos" => "id INT AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45), email VARCHAR(255), fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];

    foreach ($tablas as $nombre => $campos) { 
        $conn->query("CREATE TABLE IF NOT EXISTS `$nombre` ($campos)"); 
    }

    $conn->query("INSERT IGNORE INTO opciones (opcion_key, opcion_dato) VALUES ('estado', 'instalando')");
    return $conn;
}

// --- GESTIÓN DE OPCIONES (Protegidas contra redeclaración) ---

if (!function_exists('create_opcion')) {
    function create_opcion($opcion_key, $valor) {
        global $conexion;
        if (!$opcion_key) return false;
        $stmt = $conexion->prepare("INSERT INTO opciones (opcion_key, opcion_dato) VALUES (?, ?)");
        $stmt->bind_param("ss", $opcion_key, $valor);
        return $stmt->execute();
    }
}

if (!function_exists('update_opcion')) {
    function update_opcion($key, $valor) {
        global $conexion;
        $stmt = $conexion->prepare("UPDATE opciones SET opcion_dato = ? WHERE opcion_key = ?");
        $stmt->bind_param("ss", $valor, $key);
        return $stmt->execute();
    }
}

if (!function_exists('get_all_opciones')) {
    function get_all_opciones() {
        global $conexion;
        $opciones = [];
        $resultado = $conexion->query("SELECT opcion_key, opcion_dato FROM opciones WHERE opcion_key NOT IN ('salt_key')");
        if ($resultado) {
            while ($row = $resultado->fetch_assoc()) {
                $opciones[$row['opcion_key']] = $row['opcion_dato'];
            }
        }
        return $opciones;
    }
}

if (!function_exists('get_opcion')) {
    function get_opcion($key) {
        global $conexion;
        if (!isset($conexion) || $conexion->connect_error) return false;
        $stmt = $conexion->prepare("SELECT opcion_dato FROM opciones WHERE opcion_key = ? LIMIT 1");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res->num_rows > 0) ? $res->fetch_assoc()['opcion_dato'] : false;
    }
}

// --- FUNCIONES DE USUARIO ---

function create_user_admin($nombre, $nickname, $email, $rol, $password) {
    global $conexion; 
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
    return $stmt->execute() ? $token : false;
}

function user_existe($email) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    return ($res->num_rows > 0);
}