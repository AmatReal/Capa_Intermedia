<?php
include('../views/database.php');
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['es_vendedor']) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idChat = intval($_POST['idChat']);
    $cantidad = intval($_POST['cantidad']);
    $precioTotal = floatval($_POST['precio']);

    // Insertar mensaje con la propuesta en la tabla de mensajes
    $mensajePropuesta = "Propuesta: {$cantidad} unidades por $" . number_format($precioTotal, 2);
    $sqlMensaje = "INSERT INTO mensajes_chat (idChat, remitente, mensaje, fecha) VALUES (?, 'vendedor', ?, NOW())";
    $stmtMensaje = $conn->prepare($sqlMensaje);
    $stmtMensaje->bind_param("is", $idChat, $mensajePropuesta);
    $stmtMensaje->execute();

    // Actualizar el chat con la última propuesta
    $sqlActualizarChat = "UPDATE chats_cotizacion SET cantidad = ?, precio_total = ? WHERE idChat = ?";
    $stmtActualizar = $conn->prepare($sqlActualizarChat);
    $stmtActualizar->bind_param("idi", $cantidad, $precioTotal, $idChat);
    $stmtActualizar->execute();

    header("Location: chat_cotizacion.php?idChat=$idChat&mensaje=Propuesta enviada.");
}
?>
