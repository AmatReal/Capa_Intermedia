<?php
include('../views/database.php');
session_start();

if (!isset($_GET['idPedido'])) {
    die("No se encontró el pedido.");
}

$idPedido = $_GET['idPedido'];

// Consultar datos del pedido
$sqlPedido = "SELECT * FROM pedidos WHERE idPedido = ?";
$stmtPedido = $conn->prepare($sqlPedido);
$stmtPedido->bind_param("i", $idPedido);
$stmtPedido->execute();
$pedido = $stmtPedido->get_result()->fetch_assoc();

// Consultar detalles del pedido
$sqlDetalles = "SELECT dp.*, p.nombre_producto 
                FROM detalle_pedido dp 
                JOIN productos p ON dp.idProducto = p.idProducto 
                WHERE dp.idPedido = ?";
$stmtDetalles = $conn->prepare($sqlDetalles);
$stmtDetalles->bind_param("i", $idPedido);
$stmtDetalles->execute();
$detalles = $stmtDetalles->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Exitoso</title>
</head>
<body>
    <h1>Pedido Confirmado</h1>
    <p>ID del Pedido: <?php echo $pedido['idPedido']; ?></p>
    <p>Fecha: <?php echo $pedido['fecha']; ?></p>
    <p>Total: $<?php echo number_format($pedido['total'], 2); ?></p>
    <h2>Detalles del Pedido</h2>
    <ul>
        <?php while ($detalle = $detalles->fetch_assoc()): ?>
            <li>
                <?php echo $detalle['nombre_producto']; ?> - 
                Cantidad: <?php echo $detalle['cantidad']; ?> - 
                Precio Unitario: $<?php echo number_format($detalle['precio_unitario'], 2); ?> - 
                Subtotal: $<?php echo number_format($detalle['subtotal'], 2); ?>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
