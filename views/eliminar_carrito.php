<?php
session_start();
include('../views/database.php');

// Obtener datos del formulario
$idProducto = $_POST['idProducto'];
$user_id = $_SESSION['user_id']; // Obtener ID del usuario desde la sesión

// Eliminar producto del carrito
$sqlDelete = "DELETE FROM carrito WHERE id_usuario = ? AND id_producto = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param("ii", $user_id, $idProducto);
$stmtDelete->execute();

// Redirigir o responder con un mensaje
echo "Producto eliminado del carrito con éxito.";
header("Location: carrito.php");
exit();
