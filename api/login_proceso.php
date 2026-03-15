<?php
/* api/login_proceso.php */

/**
 * CMS BASE - Procesador de Login y Seguridad de Acceso
 */

session_start();
include_once __DIR__ . '/db.php';
include_once __DIR__ . '/../seguridad/funciones.php';
include_once __DIR__ . '/../tovi/funciones.php';

// Respuesta por defecto
$respuesta = ["status" => "error", "message" => "Acceso denegado"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = limpiar_entrada($_POST['email'] ?? '');
    $pass  = $_POST['pass'] ?? '';
    $ip    = $_SERVER['REMOTE_ADDR'];
    $ahora = time();

    // 1. Verificar Bloqueo por Fuerza Bruta (5 minutos)
    // Buscamos en la tabla opciones si esta IP está penalizada
    $stmt_lock = $conexion->prepare("SELECT opcion_dato FROM opciones WHERE opcion_key = ?");
    $key_lock = "lock_" . $ip;
    $stmt_lock->bind_param("s", $key_lock);
    $stmt_lock->execute();
    $res_lock = $stmt_lock->get_result();
    
    if ($fila_lock = $res_lock->fetch_assoc()) {
        $tiempo_bloqueo = (int)$fila_lock['opcion_dato'];
        if ($ahora < $tiempo_bloqueo) {
            $minutos_restantes = ceil(($tiempo_bloqueo - $ahora) / 60);
            echo json_encode(["status" => "error", "message" => "Demasiados intentos. Bloqueado por $minutos_restantes min."]);
            exit;
        } else {
            // El tiempo ya pasó, borramos el bloqueo
            $conexion->query("DELETE FROM opciones WHERE opcion_key = '$key_lock'");
        }
    }

    // 2. Intentar encontrar al usuario
    $stmt = $conexion->prepare("SELECT id, password, nombre, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($user = $resultado->fetch_assoc()) {
        // 3. Verificar Contraseña con BCRYPT
        if (verificar_pass($pass, $user['password'])) {
            // LOGIN EXITOSO
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            $_SESSION['last_activity'] = $ahora;

            $respuesta = ["status" => "success", "message" => "Bienvenido, " . $user['nombre']];
        } else {
            // CONTRASEÑA INCORRECTA - Registrar fallo
            registrar_fallo_login($ip, $conexion);
            $respuesta = ["status" => "error", "message" => "Credenciales inválidas"];
        }
    } else {
        // USUARIO NO EXISTE - También registramos fallo para evitar enumeración de usuarios
        registrar_fallo_login($ip, $conexion);
        $respuesta = ["status" => "error", "message" => "Credenciales inválidas"];
    }
}

echo json_encode($respuesta);

/**
 * Función interna para manejar el contador de fallos por IP
 */
function registrar_fallo_login($ip, $conexion) {
    $key_intentos = "intentos_" . $ip;
    $stmt = $conexion->prepare("SELECT id, opcion_dato FROM opciones WHERE opcion_key = ?");
    $stmt->bind_param("s", $key_intentos);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($fila = $res->fetch_assoc()) {
        $intentos = (int)$fila['opcion_dato'] + 1;
        if ($intentos >= 5) {
            // Bloqueamos por 5 minutos (300 segundos)
            $bloqueo_hasta = time() + 300;
            $conexion->query("UPDATE opciones SET opcion_key = 'lock_$ip', opcion_dato = '$bloqueo_hasta' WHERE opcion_key = '$key_intentos'");
        } else {
            $conexion->query("UPDATE opciones SET opcion_dato = '$intentos' WHERE opcion_key = '$key_intentos'");
        }
    } else {
        $conexion->query("INSERT INTO opciones (opcion_key, opcion_dato) VALUES ('$key_intentos', '1')");
    }
}