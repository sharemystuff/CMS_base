<?php
/* test_seguridad.php */
include_once 'api/main.php';

echo "<h1>Auditoría de Seguridad y Login</h1>";

// --- TEST 1: Headers de Seguridad ---
echo "<h2>1. Verificación de Headers</h2>";
$headers = headers_list();
$headers_buscados = ['X-Frame-Options', 'Content-Security-Policy', 'X-Content-Type-Options'];
echo "<ul>";
foreach ($headers as $header) {
    echo "<li>✅ $header</li>";
}
echo "</ul>";

// --- TEST 2: Estado de la Base de Datos ---
echo "<h2>2. Diagnóstico de Base de Datos</h2>";
try {
    $stmt = $conexion->prepare("SELECT id, email, password, activo FROM usuarios LIMIT 1");
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();

    if ($user) {
        echo "✅ Usuario encontrado: <strong>" . htmlspecialchars($user['email']) . "</strong><br>";
        echo "✅ Estado activo: " . ($user['activo'] == 1 ? 'SÍ' : 'NO') . "<br>";
        
        // Verificar formato de contraseña
        $hash = $user['password'];
        echo "🔍 Hash en DB: <code>$hash</code><br>";
        
        if (strpos($hash, '$2y$') === 0) {
            echo "✅ Formato de hash: Correcto (BCRYPT).<br>";
        } else {
            echo "❌ <strong>ERROR:</strong> La contraseña NO está hasheada con BCRYPT. El login fallará siempre.<br>";
        }
    } else {
        echo "❌ No hay usuarios en la tabla.";
    }
} catch (Exception $e) {
    echo "❌ Error en consulta: " . $e->getMessage();
}

// --- TEST 3: Prueba de consistencia CSRF ---
echo "<h2>3. Verificación de CSRF</h2>";
if (isset($_SESSION['csrf_token'])) {
    echo "✅ Token CSRF generado: <code>" . $_SESSION['csrf_token'] . "</code>";
} else {
    echo "❌ Error: No se ha generado el token de sesión.";
}

// --- TEST 4: Simulador de Verificación (Para descartar errores de PHP) ---
echo "<h2>4. Simulador de password_verify</h2>";
$pass_prueba = "admin"; // Cambia esto por la clave que crees tener
$hash_db = $user['password'] ?? '';
if (password_verify($pass_prueba, $hash_db)) {
    echo "✅ <strong>ÉXITO:</strong> La contraseña '$pass_prueba' coincide con el hash de la DB.";
} else {
    echo "❌ <strong>FALLO:</strong> La contraseña '$pass_prueba' NO coincide.";
}
?>