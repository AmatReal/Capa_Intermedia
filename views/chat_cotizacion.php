<?php
include('../views/database.php');
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del chat desde la URL
if (!isset($_GET['idChat'])) {
    echo "Chat no especificado.";
    exit;
}

$idChat = intval($_GET['idChat']);
$userId = $_SESSION['user_id'];
$esVendedor = isset($_SESSION['es_vendedor']) && $_SESSION['es_vendedor'];

// Verificar que el usuario tenga acceso al chat
$sqlChat = "SELECT * FROM chats_cotizacion WHERE idChat = ? AND (idCliente = ? OR idVendedor = ?)";
$stmt = $conn->prepare($sqlChat);
$stmt->bind_param("iii", $idChat, $userId, $userId);
$stmt->execute();
$resultChat = $stmt->get_result();
$chat = $resultChat->fetch_assoc();

if (!$chat) {
    echo "No tienes permiso para acceder a este chat.";
    exit;
}

// Obtener mensajes del chat
$sqlMensajes = "SELECT * FROM mensajes_chat WHERE idChat = ? ORDER BY fecha ASC";
$stmtMensajes = $conn->prepare($sqlMensajes);
$stmtMensajes->bind_param("i", $idChat);
$stmtMensajes->execute();
$resultMensajes = $stmtMensajes->get_result();

// Verificar el estado del chat
$estadoChat = $chat['estado']; // Puede ser 'abierto' o 'cerrado'
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat de Cotización</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include('../comp/header.php'); ?>

    <div class="container mt-5">
        <h2>Chat de Cotización</h2>
        <div class="chat-box border rounded p-3 mb-3" style="height: 400px; overflow-y: scroll;">
            <?php while ($mensaje = $resultMensajes->fetch_assoc()): ?>
                <div class="message mb-2">
                    <strong>
                        <?php echo ($mensaje['remitente'] === 'vendedor') ? "Vendedor:" : "Cliente:"; ?>
                    </strong>
                    <p>
                        <?php if (strpos($mensaje['mensaje'], 'Propuesta:') === 0): ?>
                            <span class="text-info"><?php echo htmlspecialchars($mensaje['mensaje']); ?></span>
                        <?php else: ?>
                            <?php echo htmlspecialchars($mensaje['mensaje']); ?>
                        <?php endif; ?>
                    </p>
                    <small class="text-muted"><?php echo $mensaje['fecha']; ?></small>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($estadoChat === 'abierto'): ?>
            <?php if ($esVendedor): ?>
                <!-- Formulario para enviar una propuesta -->
                <form action="enviar_propuesta.php" method="POST" class="mt-3">
                    <input type="hidden" name="idChat" value="<?php echo $idChat; ?>">
                    <div class="form-group">
                        <label for="cantidad">Cantidad propuesta:</label>
                        <input type="number" name="cantidad" min="1" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="precio">Precio total:</label>
                        <input type="number" step="0.01" name="precio" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Enviar Propuesta</button>
                </form>
            <?php else: ?>
                <!-- Opciones para aceptar o rechazar propuesta -->
                <?php 
                // Obtener la última propuesta del vendedor
                $sqlUltimaPropuesta = "SELECT * FROM mensajes_chat WHERE idChat = ? AND remitente = 'vendedor' AND mensaje LIKE 'Propuesta:%' ORDER BY fecha DESC LIMIT 1";
                $stmtUltimaPropuesta = $conn->prepare($sqlUltimaPropuesta);
                $stmtUltimaPropuesta->bind_param("i", $idChat);
                $stmtUltimaPropuesta->execute();
                $ultimaPropuesta = $stmtUltimaPropuesta->get_result()->fetch_assoc();

                if ($ultimaPropuesta): ?>
                    <div class="mt-3">
                        <h5>Última Propuesta:</h5>
                        <p><?php echo htmlspecialchars($ultimaPropuesta['mensaje']); ?></p>
                        <form action="procesar_propuesta.php" method="POST" class="d-inline-block">
                            <input type="hidden" name="idChat" value="<?php echo $idChat; ?>">
                            <input type="hidden" name="accion" value="aceptar">
                            <button type="submit" class="btn btn-success">Aceptar</button>
                        </form>
                        <form action="procesar_propuesta.php" method="POST" class="d-inline-block">
                            <input type="hidden" name="idChat" value="<?php echo $idChat; ?>">
                            <input type="hidden" name="accion" value="rechazar">
                            <button type="submit" class="btn btn-danger">Rechazar</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Formulario para mensajes normales -->
            <form action="enviar_mensaje.php" method="POST" class="mt-3">
                <input type="hidden" name="idChat" value="<?php echo $idChat; ?>">
                <div class="form-group">
                    <textarea name="mensaje" class="form-control" rows="3" placeholder="Escribe un mensaje..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar</button>
            </form>
        <?php else: ?>
            <div class="alert alert-info mt-3">El chat está cerrado. No se pueden enviar más mensajes.</div>
        <?php endif; ?>
    </div>

    <?php include('../comp/footer.php'); ?>
</body>
</html>