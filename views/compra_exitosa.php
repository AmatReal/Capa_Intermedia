<?php
include('../views/database.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['idPedido'])) {
    echo "Pedido no especificado.";
    exit;
}

$idPedido = intval($_GET['idPedido']);

// Consultar información del pedido
$sqlPedido = "SELECT * FROM pedidos WHERE idPedido = ?";
$stmtPedido = $conn->prepare($sqlPedido);
$stmtPedido->bind_param("i", $idPedido);
$stmtPedido->execute();
$resultPedido = $stmtPedido->get_result();
$pedido = $resultPedido->fetch_assoc();

if (!$pedido) {
    echo "Pedido no encontrado.";
    exit;
}

// Consultar detalles del pedido
$sqlDetalles = "
    SELECT v.idProducto, p.nombre_producto, v.cantidad, v.total, 
           (SELECT archivo FROM multimedia WHERE id_producto = v.idProducto AND tipo = 'imagen' LIMIT 1) AS imagen
    FROM ventas v
    INNER JOIN productos p ON v.idProducto = p.idProducto
    WHERE v.idPedido = ?";
$stmtDetalles = $conn->prepare($sqlDetalles);
$stmtDetalles->bind_param("i", $idPedido);
$stmtDetalles->execute();
$resultDetalles = $stmtDetalles->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Exitosa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .pedido-container {
            margin-top: 50px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .pedido-header {
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .producto-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .producto-info {
            display: flex;
            align-items: center;
        }

        .producto-imagen {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            margin-right: 15px;
        }

        .producto-detalles {
            flex-grow: 1;
        }
    </style>
</head>
<body>
<div class="container pedido-container">
    <div class="pedido-header">
        <h1 class="text-success">¡Compra Exitosa!</h1>
        <p>Gracias por tu compra. Aquí están los detalles de tu pedido:</p>
        <p><strong>Pedido ID:</strong> #<?php echo $pedido['idPedido']; ?></p>
        <p><strong>Fecha:</strong> <?php echo $pedido['fecha']; ?></p>
        <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 2); ?></p>
    </div>

    <h5>Productos del Pedido:</h5>
    <?php if ($resultDetalles->num_rows > 0): ?>
        <div class="list-group">
            <?php while ($detalle = $resultDetalles->fetch_assoc()): ?>
                <div class="producto-item">
                    <div class="producto-info">
                        <img src="<?php echo $detalle['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($detalle['imagen']) : 'https://via.placeholder.com/50'; ?>" 
                             alt="<?php echo htmlspecialchars($detalle['nombre_producto']); ?>" 
                             class="producto-imagen">
                        <div class="producto-detalles">
                            <strong><?php echo htmlspecialchars($detalle['nombre_producto']); ?></strong>
                            <p class="mb-0">Cantidad: <?php echo $detalle['cantidad']; ?></p>
                        </div>
                    </div>
                    <span class="badge bg-primary rounded-pill">$<?php echo number_format($detalle['total'], 2); ?></span>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning mt-3">
            No se encontraron productos asociados a este pedido.
        </div>
    <?php endif; ?>

    <a href="mis_pedidos.php" class="btn btn-primary mt-4">Ver todos mis pedidos</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
