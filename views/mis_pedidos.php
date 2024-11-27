<?php
include('../views/database.php');
session_start();
/** @var mysqli $conn */
$user_id = $_SESSION['user_id'];

// Consultar los pedidos del cliente
$sqlPedidos = "SELECT * FROM pedidos WHERE idCliente = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sqlPedidos);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pedidos = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html,
        body {
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

        footer {
            background-color: #f8f9fa;
            padding: 20px 0;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include('../comp/header.php'); ?>

        <div class="content container mt-4">
            <h1 class="mb-4">Mis Cursos</h1>

            <?php if ($pedidos->num_rows > 0): ?>
                <div class="accordion" id="accordionPedidos">
                    <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?php echo $pedido['idPedido']; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $pedido['idPedido']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $pedido['idPedido']; ?>">
                                    Pedido #<?php echo $pedido['idPedido']; ?> - Fecha: <?php echo $pedido['fecha']; ?> - Total: $<?php echo number_format($pedido['total'], 2); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $pedido['idPedido']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $pedido['idPedido']; ?>" data-bs-parent="#accordionPedidos">
                                <div class="accordion-body">
                                    <h5>Detalles del Pedido</h5>
                                    <ul class="list-group">
                                        <?php
                                        // Consultar los productos asociados al pedido (incluyendo cotizaciones aceptadas)
                                        $sqlDetalles = "
                                        SELECT * 
                                        FROM vista_detalle_ventas 
                                        WHERE id_pedido = ?";
                                        $stmtDetalles = $conn->prepare($sqlDetalles);
                                        $stmtDetalles->bind_param("i", $pedido['idPedido']);
                                        $stmtDetalles->execute();
                                        $resultDetalles = $stmtDetalles->get_result();

                                        while ($detalle = $resultDetalles->fetch_assoc()): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div class="d-flex">
                                                    <img src="<?php echo $detalle['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($detalle['imagen']) : 'https://via.placeholder.com/50'; ?>"
                                                        alt="<?php echo htmlspecialchars($detalle['nombre_producto']); ?>"
                                                        class="img-thumbnail me-3" style="width: 50px; height: 50px;">
                                                    <div>
                                                        <strong><?php echo $detalle['nombre_producto']; ?></strong>
                                                        <p class="mb-0">
                                                            <?php if ($detalle['tipo_producto'] === 'Propuesta Aceptada'): ?>
                                                                <span class="text-success"><?php echo $detalle['tipo_producto']; ?></span>
                                                            <?php else: ?>
                                                                Cantidad: <?php echo $detalle['cantidad']; ?>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <span class="badge bg-primary rounded-pill">$<?php echo number_format($detalle['total'], 2); ?></span>
                                            </li>
                                        <?php endwhile; ?>
                                        <?php $stmtDetalles->close(); ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning" role="alert">
                    No tienes pedidos registrados.
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