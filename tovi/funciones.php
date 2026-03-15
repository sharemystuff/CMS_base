<?php
/**
 * CMS BASE - FUNCIONES CORE (TOVI)
 * Funciones reutilizables para la gestión de datos y usuarios.
 */

/**
 * Registra un nuevo usuario en la base de datos. [cite: 33]
 * @param string $nombre Nombre real del usuario. [cite: 34]
 * @param string $nickname Nombre público. [cite: 34]
 * @param string $email Correo electrónico.
 * @param string $rol admin, owner o editor. [cite: 34]
 * @param string $password Contraseña que será hasheada.
 * @return int|string ID del usuario o JSON con error si falta algún dato. [cite: 35, 36]
 */
function create_user($nombre, $nickname, $email, $rol, $password) {
    global $conexion; // Reutiliza la conexión de api/db.php

    // Validaciones de seguridad solicitadas por Pelín [cite: 36]
    if (!$nombre) return json_encode(["error", "nombre"]);
    if (!in_array($rol, ['admin', 'owner', 'editor'])) return json_encode(["error", "rol"]);
    if (!$password) return json_encode(["error", "password"]);

    // Hasheamos antes de guardar para mayor seguridad.
    $pass_encoded = encode_pass($password);
    $fecha = date("Y-m-d H:i:s"); // [cite: 27]

    // Usamos Prepared Statements para evitar Inyección SQL (SQLi). 
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, nickname, email, rol, fecha, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nombre, $nickname, $email, $rol, $fecha, $pass_encoded);
    
    if ($stmt->execute()) {
        return $conexion->insert_id; // Devuelve la ID asignada por la DB. [cite: 35]
    }
    return false;
}

/**
 * Crea o guarda una opción de configuración. [cite: 37]
 * @param string $opcion El nombre (clave) de la opción.
 * @param mixed $valor El contenido de la opción.
 * @return int|bool ID de la opción creada o false en caso de error.
 */
function create_opcion($opcion, $valor) {
    global $conexion;
    if (!$opcion) return false;
    
    // Si el valor no existe, lo guardamos vacío pero creamos la opción. [cite: 37]
    $valor_final = $valor ?? "";
    
    $stmt = $conexion->prepare("INSERT INTO opciones (opcion_key, opcion_dato) VALUES (?, ?)");
    $stmt->bind_param("ss", $opcion, $valor_final);
    
    if ($stmt->execute()) {
        return $conexion->insert_id;
    }
    return false;
}

/**
 * Verifica si un usuario ya existe en el sistema. [cite: 38]
 * Puede buscar por ID numérica o por Email. [cite: 38]
 * @param mixed $user ID o Email.
 * @return bool True si existe, False si no. [cite: 40]
 */
function user_existe($user) {
    global $conexion;
    
    // Detectamos si es email o ID. [cite: 38]
    $campo = email_valido($user) ? "email" : "id";
    
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE $campo = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    // Si hay más de 0 filas, el usuario existe. [cite: 39]
    return ($resultado->num_rows > 0);
}