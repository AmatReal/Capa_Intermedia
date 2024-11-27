<?php
include('../views/database.php');
session_start();

/** @var mysqli $conn */

// Verificar que el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar si se recibió el ID del nivel
if (isset($_GET['id'])) {
    $nivelId = intval($_GET['id']);

    // Consultar detalles del nivel
    $sql = "SELECT nombre, descripcion, archivo, video FROM niveles WHERE id_nivel = ? AND status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $nivelId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($nivel = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'nombre' => $nivel['nombre'],
            'descripcion' => $nivel['descripcion'],
            'archivo' => base64_encode($nivel['archivo']),
            'video' => base64_encode($nivel['video']),
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nivel no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de nivel no proporcionado']);
}
?>
