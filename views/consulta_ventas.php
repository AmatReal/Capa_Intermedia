<?php
session_start();
include('../views/database.php');

$idVendedor = $_SESSION['user_id']; // ID del vendedor autenticado

$sqlVentas = "
    SELECT v.idVenta, v.fecha, p.nombre_producto, u.full_name AS cliente, v.cantidad, v.total, ped.fecha AS fecha_pedido
    FROM ventas v
    INNER JOIN productos p ON v.idProducto = p.idProducto
    INNER JOIN pedidos ped ON v.idPedido = ped.idPedido
    INNER JOIN usuarios u ON ped.idCliente = u.idUser
    WHERE v.idVendedor = ?
    ORDER BY v.fecha DESC
";
$stmtVentas = $conn->prepare($sqlVentas);
$stmtVentas->bind_param("i", $idVendedor);
$stmtVentas->execute();
$resultVentas = $stmtVentas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px 0;
        }

        html, body {
            height: 100%;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .content {
            flex: 1;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include('../comp/header.php'); ?>

    <div class="content container mt-4">
        <h1 class="mb-4">Consulta de Ventas</h1>

        <?php if ($resultVentas->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Venta</th>
                            <th>Fecha Venta</th>
                            <th>Producto</th>
                            <th>Cliente</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Fecha Pedido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($venta = $resultVentas->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $venta['idVenta']; ?></td>
                                <td><?php echo $venta['fecha']; ?></td>
                                <td><?php echo htmlspecialchars($venta['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($venta['cliente']); ?></td>
                                <td><?php echo $venta['cantidad']; ?></td>
                                <td>$<?php echo number_format($venta['total'], 2); ?></td>
                                <td><?php echo $venta['fecha_pedido']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                No se encontraron ventas.
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <?php include('../comp/footer.php'); ?>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
