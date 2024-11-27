<?php
session_start();
include('../views/database.php');

$user_id = $_SESSION['user_id']; // Obtener ID del usuario desde la sesión

// Consultar productos en el carrito, incluyendo propuestas aceptadas
$sqlCarrito = "
    SELECT c.id_producto, p.nombre_producto, c.cantidad, p.precio, 
           (SELECT archivo FROM multimedia WHERE id_producto = p.idProducto AND tipo = 'imagen' LIMIT 1) AS imagen,
           NULL AS propuesta
    FROM carrito c
    INNER JOIN productos p ON c.id_producto = p.idProducto
    WHERE c.id_usuario = ?

    UNION

    SELECT ch.idProducto AS id_producto, p.nombre_producto, 
           (SELECT cantidad FROM mensajes_chat WHERE idChat = ch.idChat AND remitente = 'vendedor' ORDER BY fecha DESC LIMIT 1) AS cantidad,
           (SELECT precio_total FROM mensajes_chat WHERE idChat = ch.idChat AND remitente = 'vendedor' ORDER BY fecha DESC LIMIT 1) AS precio,
           (SELECT archivo FROM multimedia WHERE id_producto = p.idProducto AND tipo = 'imagen' LIMIT 1) AS imagen,
           'propuesta' AS propuesta
    FROM chats_cotizacion ch
    INNER JOIN productos p ON ch.idProducto = p.idProducto
    WHERE ch.idCliente = ? AND ch.estado = 'cerrado'
";
/** @var mysqli $conn */

$stmtCarrito = $conn->prepare($sqlCarrito);
$stmtCarrito->bind_param("ii", $user_id, $user_id);
$stmtCarrito->execute();
$resultCarrito = $stmtCarrito->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('../comp/header.php'); ?>

    <div class="container">
        <h1 class="mb-4">Carrito</h1>
        
        <div id="cart-items">
            <?php if ($resultCarrito->num_rows > 0): ?>
                <?php while ($item = $resultCarrito->fetch_assoc()): ?>
                    <?php 
                    if ($item['propuesta'] === 'propuesta') {
                        // Producto cotizado: el precio es el precio total enviado por el vendedor
                        $subtotal = $item['precio']; // Aquí el precio ya es el total de la propuesta
                        $total += $subtotal;
                    } else {
                        // Producto normal: calcular subtotal
                        $subtotal = $item['cantidad'] * $item['precio'];
                        $total += $subtotal;
                    }
                    $imagenSrc = $item['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($item['imagen']) : 'https://via.placeholder.com/150';
                    ?>
                    <div class="cart-item row align-items-center mb-3">
                        <div class="col-3">
                            <img src="<?php echo $imagenSrc; ?>" class="img-fluid" alt="<?php echo htmlspecialchars($item['nombre_producto']); ?>">
                        </div>
                        <div class="col-5">
                            <h5><?php echo htmlspecialchars($item['nombre_producto']); ?></h5>
                            <?php if ($item['propuesta'] === 'propuesta'): ?>
                                <small class="text-success">Propuesta aceptada</small>
                            <?php else: ?>
                                <form class="d-flex align-items-center" method="POST" action="actualizar_carrito.php">
                                    <input type="hidden" name="idProducto" value="<?php echo $item['id_producto']; ?>">
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" class="form-control me-2" min="1" style="width: 100px;">
                                    <button type="submit" class="btn btn-warning">Actualizar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="col-3 text-end">
                            <?php if ($item['propuesta'] === 'propuesta'): ?>
                                <p>Precio total: $<?php echo number_format($subtotal, 2); ?></p>
                            <?php else: ?>
                                <p>Precio unitario: $<?php echo number_format($item['precio'], 2); ?></p>
                                <p>Subtotal: $<?php echo number_format($subtotal, 2); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-1 text-end">
                            <?php if ($item['propuesta'] !== 'propuesta'): ?>
                                <form method="POST" action="eliminar_carrito.php">
                                    <input type="hidden" name="idProducto" value="<?php echo $item['id_producto']; ?>">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                <div class="row my-4">
                    <div class="col-12 text-end total">
                        <h4>Total: $<?php echo number_format($total, 2); ?></h4>
                    </div>
                </div>
            <?php else: ?>
                <p>No hay productos en el carrito.</p>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <form method="POST" action="procesar_compra.php">
                <input type="hidden" name="total_compra" value="<?php echo $total; ?>">
                <button type="submit" class="btn btn-primary">Comprar</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <?php include('../comp/footer.php'); ?>
</body>
</html>
