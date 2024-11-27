<?php
session_start();
include('../views/database.php');

// Obtener datos del formulario
$idProducto = $_POST['idProducto'];
$nuevaCantidad = $_POST['cantidad'];
$user_id = $_SESSION['user_id'];// Obtener ID del usuario desde la sesión

// Actualizar la cantidad en el carrito
$sqlUpdate = "UPDATE carrito SET cantidad = ? WHERE id_usuario = ? AND id_producto = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("iii", $nuevaCantidad, $user_id, $idProducto);
$stmtUpdate->execute();

// Redirigir o responder con un mensaje
echo "Carrito actualizado con éxito.";
header("Location: carrito.php");
exit();
