<?php
include('../views/database.php');
/** @var mysqli $conn */
if (isset($_POST['id_nivel']) && isset($_POST['status'])) {
    $id_nivel = intval($_POST['id_nivel']);
    $status = intval($_POST['status']);
    
    $sql = "UPDATE niveles SET status = ? WHERE id_nivel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $status, $id_nivel);
    if ($stmt->execute()) {
        echo "Estado actualizado";
    } else {
        echo "Error al actualizar";
    }
}
?>
