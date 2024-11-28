<?php
include('../views/database.php');
session_start();
/** @var mysqli $conn */
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
                        <?php echo ($mensaje['remitente'] === 'vendedor') ? "Maestro:" : "Alumno:"; ?>
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
