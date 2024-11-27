<?php 
include('../views/database.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['es_vendedor']) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idChat = intval($_POST['idChat']);
    $accion = $_POST['accion'];

    // Verificar que el chat está abierto
    $sql = "SELECT * FROM chats_cotizacion WHERE idChat = ? AND estado = 'abierto'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idChat);
    $stmt->execute();
    $chat = $stmt->get_result()->fetch_assoc();

    if (!$chat) {
        echo "Chat no encontrado o ya cerrado.";
        exit;
    }

    if ($accion === 'aceptar') {
        // Obtener la última propuesta del vendedor
        $sqlPropuesta = "SELECT * FROM mensajes_chat WHERE idChat = ? AND remitente = 'vendedor' ORDER BY fecha DESC LIMIT 1";
        $stmt = $conn->prepare($sqlPropuesta);
        $stmt->bind_param("i", $idChat);
        $stmt->execute();
        $propuesta = $stmt->get_result()->fetch_assoc();

        // Extraer cantidad y precio de la propuesta
        preg_match('/(\d+) unidades por \$([\d.]+)/', $propuesta['mensaje'], $matches);
        $cantidad = intval($matches[1]);
        $precio_total = floatval($matches[2]);

        $idCliente = $_SESSION['user_id'];
        $idProducto = $chat['idProducto'];

        // **Evitar duplicados en el carrito**
        // Verificar si ya existe un registro para este producto en la tabla carrito
        $sqlVerificar = "SELECT id_producto FROM carrito WHERE id_usuario = ? AND id_producto = ?";
        $stmtVerificar = $conn->prepare($sqlVerificar);
        $stmtVerificar->bind_param("ii", $idCliente, $idProducto);
        $stmtVerificar->execute();
        $resultVerificar = $stmtVerificar->get_result();

        if ($resultVerificar->num_rows === 0) {
            // Añadir al carrito
            $sqlCarrito = "INSERT INTO carrito (id_usuario, id_producto, cantidad, fecha_agregado) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sqlCarrito);
            $stmt->bind_param("iii", $idCliente, $idProducto, $cantidad);
            $stmt->execute();
        }

        // Cerrar el chat
        $sqlCerrarChat = "UPDATE chats_cotizacion SET estado = 'cerrado' WHERE idChat = ?";
        $stmt = $conn->prepare($sqlCerrarChat);
        $stmt->bind_param("i", $idChat);
        $stmt->execute();

        header("Location: chat_cotizacion.php?idChat=$idChat&mensaje=Propuesta aceptada y añadida al carrito.");
    } elseif ($accion === 'rechazar') {
        header("Location: chat_cotizacion.php?idChat=$idChat&mensaje=Propuesta rechazada.");
    }
}
?>
