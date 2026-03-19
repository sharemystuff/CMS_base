<?php
/* admin/admin-ajax.php */
include_once __DIR__ . '/../api/main.php';

// Respuesta siempre en JSON
header('Content-Type: application/json');

// 1. Seguridad: Solo usuarios logueados
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada']);
    exit;
}

// 2. Seguridad: Token CSRF obligatorio
if (!isset($_POST['csrf_token']) || !validarCSRF($_POST['csrf_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Token de seguridad inválido']);
    exit;
}

// 3. Enrutador de Acciones
if (isset($_POST['action'])) {
    
    if ($_POST['action'] === 'guardar_modo') {
        $modo = ($_POST['modo'] === 'oscuro') ? 'oscuro' : 'claro';
        
        // Actualizamos DB y también la SESIÓN para que persista sin reloguear
        $res = actualizar_preferencia_admin($_SESSION['user_id'], $modo);
        $_SESSION['user_modo'] = $modo;

        echo json_encode(['status' => 'success', 'guardado' => $res]);
        exit;
    }

    if ($_POST['action'] === 'subir_imagen_perfil') {
        if (empty($_POST['imagen'])) {
            echo json_encode(['status' => 'error', 'message' => 'No se recibió ninguna imagen']);
            exit;
        }
        $resultado = procesar_avatar($_SESSION['user_id'], $_POST['imagen']);
        echo json_encode($resultado);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Acción no reconocida']);