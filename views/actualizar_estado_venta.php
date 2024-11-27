<?php
// Incluir la conexión a la base de datos
include('../views/database.php');
session_start();
/** @var mysqli $conn */

// Verificar que los datos estén disponibles
if (isset($_POST['idProducto']) && isset($_POST['userId'])) {
    $idProducto = intval($_POST['idProducto']);  // Sanitizar el idProducto
    $userId = intval($_POST['userId']);  // Sanitizar el userId

    // Actualizar el estado de la venta en la tabla 'ventas' a 'Finalizado'
    $sql = "UPDATE ventas SET estado = 'Finalizado' WHERE idProducto = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idProducto, $userId);

    if ($stmt->execute()) {
        echo "Estado de la venta actualizado a 'Finalizado'.";
    } else {
        echo "Error al actualizar el estado.";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
} else {
    echo "Faltan parámetros.";
}
?>
