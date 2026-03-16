<?php
/* api/subir_foto.php */
include_once __DIR__ . '/main.php';

// Solo usuarios logueados pueden subir fotos
if (!checking()) {
    http_response_code(403);
    die(json_encode(["status" => "error", "message" => "No autorizado"]));
}

$resp = ["status" => "error", "message" => "Error al procesar la subida"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    
    // Validar Token CSRF antes de procesar nada
    $token = $_POST['csrf_token'] ?? '';
    if (!validarCSRF($token)) {
        http_response_code(403);
        die(json_encode(["status" => "error", "message" => "Token CSRF inválido"]));
    }

    $file = $_FILES['foto'];
    $nombre_tmp = $file['tmp_name'];
    $nombre_orig = $file['name'];

    // --- CAPA DE SEGURIDAD 1: Extensión (Lista Blanca) ---
    $ext = strtolower(pathinfo($nombre_orig, PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($ext, $permitidos)) {
        die(json_encode(["status" => "error", "message" => "Extensión .$ext no permitida"]));
    }

    // --- CAPA DE SEGURIDAD 2: Contenido Real (MIME Type) ---
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $nombre_tmp);
    finfo_close($finfo);

    $mimes_ok = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime, $mimes_ok)) {
        die(json_encode(["status" => "error", "message" => "El contenido del archivo no es una imagen real"]));
    }

    // --- CAPA DE SEGURIDAD 3: Renombrado y Ruta Segura ---
    // Nunca confíes en el nombre que envía el usuario
    $nuevo_nombre = bin2hex(random_bytes(10)) . "." . $ext;
    
    // Asegúrate de crear la carpeta 'uploads/perfiles/' en la raíz
    $destino = __DIR__ . "/../uploads/perfiles/" . $nuevo_nombre;

    if (move_uploaded_file($nombre_tmp, $destino)) {
        $resp = ["status" => "success", "message" => "Imagen subida", "url" => "/uploads/perfiles/$nuevo_nombre"];
    }
}

echo json_encode($resp);