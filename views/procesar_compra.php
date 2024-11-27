<?php
include('../views/database.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Consultar productos del carrito, incluyendo propuestas aceptadas
$sqlCarrito = "
    SELECT c.id_producto, c.cantidad, p.tipo_venta, 
           (CASE 
               WHEN p.tipo_venta = 'cotizar' THEN 
                   (SELECT precio_total FROM mensajes_chat 
                    WHERE idChat = ch.idChat 
                    AND remitente = 'vendedor' 
                    ORDER BY fecha DESC LIMIT 1)
               ELSE p.precio
           END) AS precio_unitario
    FROM carrito c
    INNER JOIN productos p ON c.id_producto = p.idProducto
    LEFT JOIN chats_cotizacion ch ON ch.idProducto = c.id_producto AND ch.idCliente = ?
    WHERE c.id_usuario = ?

    UNION ALL

    SELECT ch.idProducto AS id_producto, 
           (SELECT cantidad FROM mensajes_chat 
            WHERE idChat = ch.idChat AND remitente = 'vendedor' ORDER BY fecha DESC LIMIT 1) AS cantidad, 
           'cotizar' AS tipo_venta,
           (SELECT precio_total FROM mensajes_chat 
            WHERE idChat = ch.idChat AND remitente = 'vendedor' ORDER BY fecha DESC LIMIT 1) AS precio_unitario
    FROM chats_cotizacion ch
    WHERE ch.idCliente = ? AND ch.estado = 'cerrado'
";

$stmtCarrito = $conn->prepare($sqlCarrito);
$stmtCarrito->bind_param("iii", $user_id, $user_id, $user_id);
$stmtCarrito->execute();
$resultCarrito = $stmtCarrito->get_result();

if ($resultCarrito->num_rows === 0) {
    echo "No hay productos en el carrito.";
    exit;
}

// Calcular el total de la compra y recopilar productos
$totalCompra = 0;
$productosCarrito = [];
while ($row = $resultCarrito->fetch_assoc()) {
    $precioUnitario = floatval($row['precio_unitario']);
    $cantidad = intval($row['cantidad']);
    $subtotal = $precioUnitario * $cantidad;

    $productosCarrito[] = [
        'id_producto' => $row['id_producto'],
        'cantidad' => $cantidad,
        'precio_unitario' => $precioUnitario,
        'subtotal' => $subtotal
    ];

    $totalCompra += $subtotal;
}

// Crear un nuevo pedido
$sqlPedido = "INSERT INTO pedidos (idCliente, total) VALUES (?, ?)";
$stmtPedido = $conn->prepare($sqlPedido);
$stmtPedido->bind_param("id", $user_id, $totalCompra);
$stmtPedido->execute();

$idPedido = $stmtPedido->insert_id; // Obtener el ID del pedido recién creado

if (!$idPedido) {
    echo "Error al crear el pedido.";
    exit;
}

// Insertar los productos del carrito en la tabla de ventas
$sqlVenta = "INSERT INTO ventas (idPedido, idVendedor, idProducto, cantidad, total) 
             SELECT ?, p.id_vendedor, ?, ?, ? FROM productos p WHERE p.idProducto = ?";
$stmtVenta = $conn->prepare($sqlVenta);

foreach ($productosCarrito as $producto) {
    $idProducto = $producto['id_producto'];
    $cantidad = $producto['cantidad'];
    $precioUnitario = $producto['precio_unitario'];
    $subtotal = $producto['subtotal'];

    $stmtVenta->bind_param("iiidi", $idPedido, $idProducto, $cantidad, $subtotal, $idProducto);
    $stmtVenta->execute();
}

// Limpiar los productos normales del carrito
$sqlLimpiarCarrito = "DELETE FROM carrito WHERE id_usuario = ?";
$stmtLimpiarCarrito = $conn->prepare($sqlLimpiarCarrito);
$stmtLimpiarCarrito->bind_param("i", $user_id);
$stmtLimpiarCarrito->execute();

// Limpiar los productos de cotización del carrito
$sqlLimpiarCotizaciones = "UPDATE chats_cotizacion SET estado = 'procesado' WHERE idCliente = ? AND estado = 'cerrado'";
$stmtLimpiarCotizaciones = $conn->prepare($sqlLimpiarCotizaciones);
$stmtLimpiarCotizaciones->bind_param("i", $user_id);
$stmtLimpiarCotizaciones->execute();

// Redirigir a la página de éxito
header("Location: compra_exitosa.php?idPedido=$idPedido");
exit;
?>
