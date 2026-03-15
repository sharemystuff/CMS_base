<?php
/**
 * CMS BASE - INSTALADOR "PACHECO"
 * Maneja la creación de tablas y la configuración inicial de MySql. [cite: 59, 66]
 */

/**
 * Ejecuta la creación de la estructura de la DB. [cite: 66]
 * Utiliza los nombres de campos exactos definidos en el contexto. [cite: 26]
 * @param array $datos_db Array con host, usuario, password y nombre de la DB. [cite: 62]
 * @return mysqli|bool Objeto de conexión si tuvo éxito, false si no. [cite: 65]
 */
function pacheco_instalar($datos_db) {
    // Intentamos la conexión al servidor MySQL. [cite: 64]
    $conn = @new mysqli($datos_db['host'], $datos_db['user'], $datos_db['pass']);
    
    if ($conn->connect_error) return false;

    // Creamos la base de datos si no existe. [cite: 66]
    $conn->query("CREATE DATABASE IF NOT EXISTS " . $datos_db['name']);
    $conn->select_db($datos_db['name']);

    /**
     * Definición de tablas según el documento de contexto de Pelín.
     * Respetamos nombres de campos y tipos de datos. [cite: 26, 27, 28]
     */
    $tablas = [
        "usuarios" => "id INT AUTO_INCREMENT PRIMARY KEY, nombre TEXT, nickname TEXT, email TEXT, rol TEXT, fecha DATETIME, password TEXT, special_key TEXT",
        "opciones" => "id INT AUTO_INCREMENT PRIMARY KEY, opcion_id VARCHAR(255), opcion_key VARCHAR(255), opcion_dato TEXT",
        "posts" => "id INT AUTO_INCREMENT PRIMARY KEY, tipo TEXT, titulo TEXT, url TEXT, contenido TEXT, opengraph TEXT, imagen TEXT, fecha DATETIME, autor INT, categorias TEXT, etiquetas TEXT",
        "user_meta" => "id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, user_key TEXT, user_dato TEXT",
        "post_meta" => "id INT AUTO_INCREMENT PRIMARY KEY, post_id INT, post_key TEXT, post_dato TEXT"
    ];

    // Ejecutamos la creación de cada tabla.
    foreach ($tablas as $nombre => $campos) {
        $conn->query("CREATE TABLE IF NOT EXISTS $nombre ($campos)");
    }

    return $conn;
}