<?php
/* public/login.php */
include_once '../api/main.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar CSRF (Punto 8.3)
    if (!isset($_POST['csrf_token']) || !validarCSRF($_POST['csrf_token'])) {
        die("Error de validación de formulario (CSRF).");
    }

    // 2. Sanitización (Punto 8.1)
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pass = $_POST['password'];

    // 3. Prepared Statement (Punto 8.2)
    $stmt = $conexion->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // 4. Verificación de Hash (Punto 8.2 b)
        if (password_verify($pass, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol'] = $user['rol'];
            
            header("Location: ../admin/admin.php");
            exit;
        }
    }

    // Retardo para prevenir fuerza bruta (Punto 8.4)
    sleep(2);
    // Mensaje genérico para evitar enumeración (Punto 8.5)
    $error = "Credenciales incorrectas o cuenta inactiva.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - CMS BASE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div style="max-width:350px; margin:100px auto; padding:20px; border:1px solid #ccc; border-radius:8px;">
        <h2>Acceso Privado</h2>
        <?php if($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <label>Email:</label><br>
            <input type="email" name="email" required style="width:100%;"><br><br>
            
            <label>Contraseña:</label><br>
            <input type="password" name="password" required style="width:100%;"><br><br>
            
            <button type="submit" style="width:100%;">Entrar</button>
        </form>
        <p><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
    </div>
</body>
</html>