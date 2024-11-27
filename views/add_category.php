<?php
include('../views/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevaCategoria'])) {
    $nuevaCategoria = trim($_POST['nuevaCategoria']);

    if (empty($nuevaCategoria)) {
        echo json_encode(['status' => 'error', 'message' => 'El nombre de la categoría no puede estar vacío.']);
        exit;
    }

    // Verificar si la categoría ya existe
    $sqlVerificar = "SELECT idCat FROM categorias WHERE nombre_categoria = ?";
    $stmt = $conn->prepare($sqlVerificar);
    $stmt->bind_param("s", $nuevaCategoria);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'La categoría ya existe.']);
        exit;
    }

    // Insertar nueva categoría
    $sqlInsertar = "INSERT INTO categorias (nombre_categoria) VALUES (?)";
    $stmtInsertar = $conn->prepare($sqlInsertar);
    $stmtInsertar->bind_param("s", $nuevaCategoria);
    $stmtInsertar->execute();

    if ($stmtInsertar->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'idCat' => $conn->insert_id, 'nombre_categoria' => $nuevaCategoria]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al insertar la categoría.']);
    }

    $stmtInsertar->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida.']);
}
?>
