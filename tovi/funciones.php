<?php
/* tovi/funciones.php */

/**
 * CMS BASE - FUNCIONES CORE (TOVI)
 */

/**
 * Ejecuta la creación de la estructura de la DB.
 */
function pacheco_instalar($datos_db) {
    $conn = @new mysqli($datos_db['host'], $datos_db['user'], $datos_db['pass']);
    
    if ($conn->connect_error) {
        return false;
    }

    $db_name = $conn->real_escape_string($datos_db['name']);
    $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db($db_name);

    // Actualizado: Agregada columna session_token a usuarios
    $tablas = [
        "usuarios" => "id INT AUTO_INCREMENT PRIMARY KEY, nombre TEXT, nickname TEXT, email TEXT, password TEXT, rol ENUM('admin', 'owner', 'editor'), fecha DATETIME, special_key TEXT, session_token VARCHAR(255)",
        "opciones" => "id INT AUTO_INCREMENT PRIMARY KEY, opcion_id VARCHAR(255), opcion_key VARCHAR(255), opcion_dato TEXT",
        "posts" => "id INT AUTO_INCREMENT PRIMARY KEY, tipo TEXT, titulo TEXT, url TEXT, contenido TEXT, opengraph TEXT, imagen TEXT, fecha DATETIME, autor INT, categorias TEXT, etiquetas TEXT",
        "user_meta" => "id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, user_key TEXT, user_dato TEXT",
        "post_meta" => "id INT AUTO_INCREMENT PRIMARY KEY, post_id INT, post_key TEXT, post_dato TEXT"
    ];

    foreach ($tablas as $nombre => $campos) {
        $conn->query("CREATE TABLE IF NOT EXISTS `$nombre` ($campos)");
    }

    return $conn;
}

function create_user($nombre, $nickname, $email, $rol, $password) {
    global $conexion; 
    if (!$nombre || !$nickname || !$email || !$password) return false;
    $pass_encoded = encode_pass($password);
    $fecha = date("Y-m-d H:i:s");
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_encoded);
    if ($stmt->execute()) { return $conexion->insert_id; }
    return false;
}

function create_opcion($opcion_key, $valor) {
    global $conexion;
    if (!$opcion_key) return false;
    $valor_final = $valor ?? "";
    $stmt = $conexion->prepare("INSERT INTO opciones (opcion_key, opcion_dato) VALUES (?, ?)");
    $stmt->bind_param("ss", $opcion_key, $valor_final);
    if ($stmt->execute()) { return $conexion->insert_id; }
    return false;
}

function user_existe($user) {
    global $conexion;
    $campo = email_valido($user) ? "email" : "id";
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE $campo = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return ($resultado->num_rows > 0);
}