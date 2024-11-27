<?php
include('../views/database.php');
session_start();

/** @var mysqli $conn */

// Verificar que se recibió el ID del nivel
if (isset($_POST['id_nivel'])) {
    $nivelId = intval($_POST['id_nivel']);

    // Consulta para obtener los detalles del nivel
    $sql = "SELECT id_nivel, nombre, descripcion, archivo, video, status FROM niveles WHERE id_nivel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $nivelId);
    $stmt->execute();
    $result = $stmt->get_result();
    $nivel = $result->fetch_assoc();

    if ($nivel) {
        echo json_encode(['success' => true, 'nivel' => $nivel]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nivel no encontrado.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de nivel no proporcionado.']);
}
?>
