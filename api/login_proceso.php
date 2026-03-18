<?php
/* api/login_proceso.php */
include_once __DIR__ . '/main.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usamos el parámetro false en limpiar_entrada para el password
    $email = limpiar_entrada($_POST['email'] ?? '');
    $pass = limpiar_entrada($_POST['pass'] ?? '', true); 
    $recuerdame = isset($_POST['recuerdame']);

    if (!$conexion) {
        die('Error de conexión.');
    }

    // Usamos la función centralizada de login de seguridad/funciones.php
    if (login($email, $pass)) {
        // Si el login fue exitoso, manejamos el "recuérdame"
        if ($recuerdame) {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $dias = get_opcion('recuerdame') ? (int) get_opcion('recuerdame') : 30;
            
            $stmt_token = $conexion->prepare('UPDATE usuarios SET session_token = ? WHERE id = ?');
            $stmt_token->bind_param('si', $token_hash, $_SESSION['user_id']);
            $stmt_token->execute();
            
            $is_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            setcookie('session_token', $token, time() + (86400 * $dias), '/', '', $is_secure, true);
        }

        header('Location: ../admin/admin.php');
        exit;
    } else {
        // El sleep(2) ya ocurre dentro de la función login()
        header('Location: ../public/login.php?error=1');
        exit;
    }
}
?>