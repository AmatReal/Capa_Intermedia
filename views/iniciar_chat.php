<?php
include('../views/database.php');
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar que se reciben los datos necesarios
if (!isset($_POST['idProducto']) || !isset($_POST['idVendedor'])) {
    echo "Error: Datos incompletos para iniciar el chat.";
    exit;
}

$idCliente = $_SESSION['user_id']; // ID del usuario actual
$idProducto = intval($_POST['idProducto']);
$idVendedor = intval($_POST['idVendedor']);

// Verificar si ya existe un chat para este cliente, vendedor y producto
$sqlBuscarChat = "SELECT idChat FROM chats_cotizacion WHERE idProducto = ? AND idCliente = ? AND idVendedor = ?";
$stmtBuscarChat = $conn->prepare($sqlBuscarChat);
$stmtBuscarChat->bind_param("iii", $idProducto, $idCliente, $idVendedor);
$stmtBuscarChat->execute();
$resultBuscarChat = $stmtBuscarChat->get_result();

if ($resultBuscarChat->num_rows > 0) {
    // Si el chat ya existe, redirigir al cliente al chat
    $chat = $resultBuscarChat->fetch_assoc();
    header("Location: chat_cotizacion.php?idChat=" . $chat['idChat']);
    exit;
}

// Si el chat no existe, crearlo
$sqlCrearChat = "INSERT INTO chats_cotizacion (idCliente, idVendedor, idProducto, creado_en) VALUES (?, ?, ?, NOW())";
$stmtCrearChat = $conn->prepare($sqlCrearChat);
$stmtCrearChat->bind_param("iii", $idCliente, $idVendedor, $idProducto);

if ($stmtCrearChat->execute()) {
    // Obtener el ID del chat recién creado
    $idChat = $conn->insert_id;

    // Redirigir al cliente al nuevo chat
    header("Location: chat_cotizacion.php?idChat=" . $idChat);
    exit;
} else {
    echo "Error al crear el chat: " . $conn->error;
}
?>
