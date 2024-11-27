<?php
include('../views/database.php');
session_start();

$user_id = $_SESSION['user_id'];
$productoId = intval($_POST['idProducto']); // ID del producto enviado desde el formulario
$valoracion = intval($_POST['valoracion']); // Valoración (1 a 5)

// Verificar si el usuario ha comprado el producto
$sqlCompra = "
    SELECT COUNT(*) AS comprado
    FROM ventas v
    INNER JOIN pedidos p ON v.idPedido = p.idPedido
    WHERE p.idCliente = ? AND v.idProducto = ?";
$stmtCompra = $conn->prepare($sqlCompra);
$stmtCompra->bind_param("ii", $user_id, $productoId);
$stmtCompra->execute();
$resultCompra = $stmtCompra->get_result();
$compra = $resultCompra->fetch_assoc();

if ($compra['comprado'] > 0) {
    // Verificar si ya existe una valoración
    $sqlCheck = "SELECT COUNT(*) AS existe FROM valoraciones WHERE idProducto = ? AND idUsuario = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("ii", $productoId, $user_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $existe = $resultCheck->fetch_assoc();

    if ($existe['existe'] > 0) {
        // Actualizar valoración existente
        $sqlUpdate = "UPDATE valoraciones SET valoracion = ?, fecha = CURRENT_TIMESTAMP WHERE idProducto = ? AND idUsuario = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("iii", $valoracion, $productoId, $user_id);
        $stmtUpdate->execute();
    } else {
        // Insertar nueva valoración
        $sqlInsert = "INSERT INTO valoraciones (idProducto, idUsuario, valoracion) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("iii", $productoId, $user_id, $valoracion);
        $stmtInsert->execute();
    }
    header("Location: producto.php?id=$productoId&mensaje=valoracion_guardada");
} else {
    header("Location: producto.php?id=$productoId&error=no_comprado");
}
?>
