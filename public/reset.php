<?php
/* public/reset.php */
include_once __DIR__ . '/../api/main.php';

// 1. Seguridad: Si ya tiene sesión, no tiene sentido estar aquí
if (checking()) {
    header("Location: ../admin/admin.php");
    exit;
}

// 2. Bloqueo de caché e historial
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$mensaje = "";
$tipo_alerta = "";
$token_valido = false;
$user_data = null;

// 3. Verificar el token que viene por la URL (?token=xxx)
$token_url = limpiar_entrada($_GET['token'] ?? '');

if ($token_url) {
    // Usamos la función unificada de tu modelo
    $user_data = validar_token_recuperacion($token_url);
    if ($user_data) {
        $token_valido = true;
    } else {
        $mensaje = "El enlace de recuperación es inválido o ha expirado.";
        $tipo_alerta = "error";
    }
} else {
    header("Location: login.php");
    exit;
}

// 4. Procesar el cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    if (validarCSRF($_POST['csrf_token'] ?? '')) {
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';

        if (strlen($pass1) < 8) {
            $mensaje = "La contraseña debe tener al menos 8 caracteres.";
            $tipo_alerta = "error";
        } elseif ($pass1 !== $pass2) {
            $mensaje = "Las contraseñas no coinciden.";
            $tipo_alerta = "error";
        } else {
            // Acción Atómica: Actualiza pass y limpia tokens
            if (actualizar_password_recuperada($token_url, $pass1)) {
                // Éxito: Redirigimos al login con mensaje
                header("Location: login.php?msg=reset_success");
                exit;
            } else {
                $mensaje = "Error al actualizar la contraseña. Intenta más tarde.";
                $tipo_alerta = "error";
            }
        }
    } else {
        $mensaje = "Error de validación CSRF.";
        $tipo_alerta = "error";
    }
}

// Sugerencia de clave segura usando tu función core
$sugerencia = random_pass(14);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - CMS BASE</title>
    <link rel="stylesheet" href="<?php echo asset('assets/css/estilos.css'); ?>">
    <style>
        .ojo { cursor: pointer; float: right; margin-top: -30px; margin-right: 10px; opacity: 0.5; }
        .sugerencia-box { background: #f4f4f4; padding: 10px; border-radius: 5px; font-size: 0.8rem; margin-top: 10px; border: 1px dashed #ccc; }
    </style>
</head>
<body class="flex-center">

    <div class="caja login-box">
        <div class="txt-centro">
            <h1>Nueva Contraseña</h1>
            <p class="txt-muted">Hola, estás restableciendo la cuenta: <strong><?php echo e($user_data['email'] ?? ''); ?></strong></p>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($token_valido): ?>
        <form method="POST" id="formReset">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="grupo-campo">
                <label>Nueva Contraseña</label>
                <input type="password" name="pass1" id="pass1" class="campo" required placeholder="Mínimo 8 caracteres">
            </div>

            <div class="grupo-campo">
                <label>Confirmar Contraseña</label>
                <input type="password" name="pass2" id="pass2" class="campo" required placeholder="Repite la contraseña">
            </div>

            <div class="sugerencia-box">
                Sugerencia segura: <strong id="txtSugerencia"><?php echo $sugerencia; ?></strong> 
                <br>
                <a href="#" onclick="usarSugerencia('<?php echo $sugerencia; ?>'); return false;" class="enlace" style="font-size: 0.75rem;">Usar esta y copiar</a>
            </div>
            
            <button type="submit" class="boton btn-block mt-20">GUARDAR NUEVA CLAVE</button>
        </form>
        <?php else: ?>
            <div class="txt-centro mt-20">
                <a href="recuperar.php" class="boton">Solicitar nuevo enlace</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function usarSugerencia(pass) {
        document.getElementById('pass1').value = pass;
        document.getElementById('pass2').value = pass;
        document.getElementById('pass1').type = 'text';
        document.getElementById('pass2').type = 'text';
        
        // Copiar al portapapeles
        navigator.clipboard.writeText(pass).then(() => {
            alert("Contraseña aplicada y copiada al portapapeles.");
        });
    }
    </script>
</body>
</html>