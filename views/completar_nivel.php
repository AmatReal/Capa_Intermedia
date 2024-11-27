<?php
include('../views/database.php');
session_start();
/** @var mysqli $conn */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id_nivel = intval($data['id_nivel']);

    $sql = "UPDATE niveles SET status = 1 WHERE id_nivel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_nivel);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    exit;
}
?>
