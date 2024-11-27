<?php
include('../views/database.php');
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$idChat = intval($_POST['idChat']);
$mensaje = htmlspecialchars($_POST['mensaje'], ENT_QUOTES, 'UTF-8');
$userId = $_SESSION['user_id'];
$remitente = isset($_SESSION['es_vendedor']) && $_SESSION['es_vendedor'] ? 'vendedor' : 'cliente';
// Obtener el rol del usuario en el chat
$sqlRol = "SELECT idVendedor FROM chats_cotizacion WHERE idChat = ?";
$stmtRol = $conn->prepare($sqlRol);
$stmtRol->bind_param("i", $idChat);
$stmtRol->execute();
$resultRol = $stmtRol->get_result();
$chatData = $resultRol->fetch_assoc();

if ($chatData['idVendedor'] == $userId) {
    $remitente = 'vendedor';
} else {
    $remitente = 'cliente';
}

// Verificar que el usuario tiene acceso al chat
$sqlVerificar = "SELECT idChat FROM chats_cotizacion WHERE idChat = ? AND (idCliente = ? OR idVendedor = ?)";
$stmtVerificar = $conn->prepare($sqlVerificar);
$stmtVerificar->bind_param("iii", $idChat, $userId, $userId);
$stmtVerificar->execute();
$resultVerificar = $stmtVerificar->get_result();

if ($resultVerificar->num_rows === 0) {
    echo "No tienes permiso para enviar mensajes en este chat.";
    exit;
}

// Insertar el mensaje en la tabla
$sqlInsert = "INSERT INTO mensajes_chat (idChat, remitente, mensaje) VALUES (?, ?, ?)";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("iss", $idChat, $remitente, $mensaje);
$stmtInsert->execute();

header("Location: chat_cotizacion.php?idChat=$idChat");
exit;
