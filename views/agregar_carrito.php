<?php
session_start();
include('../views/database.php');

// Obtener datos del formulario
$idProducto = $_POST['idProducto'];
$cantidad = $_POST['cantidad'];

// Suponiendo que tienes un sistema de usuarios con sesión activa
$user_id = $_SESSION['user_id'];// Asegúrate de que el ID del usuario esté almacenado en la sesión

// Verificar si el producto ya está en el carrito
$sqlCheck = "SELECT cantidad FROM carrito WHERE id_usuario = ? AND id_producto = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $user_id, $idProducto);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    // Actualizar cantidad si ya existe
    $row = $resultCheck->fetch_assoc();
    $nuevaCantidad = $row['cantidad'] + $cantidad;

    $sqlUpdate = "UPDATE carrito SET cantidad = ? WHERE id_usuario = ? AND id_producto = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("iii", $nuevaCantidad, $user_id, $idProducto);
    $stmtUpdate->execute();
} else {
    // Insertar nuevo producto en el carrito
    $sqlInsert = "INSERT INTO carrito (id_usuario, id_producto, cantidad) VALUES (?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("iii", $user_id, $idProducto, $cantidad);
    $stmtInsert->execute();
}

// Redirigir o responder con un mensaje
echo "Producto agregado al carrito con éxito.";
header("Location: carrito.php");
exit();
