<?php
/* admin/logout.php */

/**
 * CMS BASE - Cierre de Sesión Seguro
 * Borra todo rastro de la sesión del usuario en el servidor.
 */

session_start();

// 1. Limpiamos todas las variables de sesión
$_SESSION = array();

// 2. Si se desea destruir la cookie de sesión también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruimos la sesión física en el servidor
session_destroy();

// 4. Redirigimos al login con un parámetro de aviso
header("Location: ../public/login.php?mensaje=logout_exitoso");
exit;