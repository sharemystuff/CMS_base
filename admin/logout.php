<?php
/**
 * CMS BASE - Cierre de Sesión Seguro
 */
include_once '../api/main.php';

// 1. Si hay una sesión activa, borramos el token de la base de datos
// Esto evita que la persistencia te vuelva a loguear
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $null_token = null;
    $stmt = $conexion->prepare("UPDATE usuarios SET session_token = ? WHERE id = ?");
    $stmt->bind_param("si", $null_token, $user_id);
    $stmt->execute();
}

// 2. Limpiamos todas las variables de sesión
$_SESSION = array();

// 3. Destruimos la cookie de sesión (PHPSESSID)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. MATAMOS la cookie de persistencia (session_token)
// Esto es lo que estaba fallando
if (isset($_COOKIE['session_token'])) {
    setcookie('session_token', '', time() - 42000, '/');
}

// 5. Destruimos la sesión en el servidor
session_destroy();

// 6. Redirigimos
header("Location: ../public/login.php?mensaje=logout_exitoso");
exit;