<?php
// Iniciar la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
include('../views/database.php');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Variable para almacenar los chats
$chats = [];

// Consulta de chats según el rol del usuario
if ($role === 'cliente') {
    $sql = "SELECT c.idChat, v.username AS vendedor_nombre, c.creado_en 
            FROM chats_cotizacion c
            JOIN usuarios v ON c.idVendedor = v.idUser
            WHERE c.idCliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $chats = $result->fetch_all(MYSQLI_ASSOC);
} elseif ($role === 'vendedor') {
    $sql = "SELECT c.idChat, cl.username AS cliente_nombre, c.creado_en 
            FROM chats_cotizacion c
            JOIN usuarios cl ON c.idCliente = cl.idUser
            WHERE c.idVendedor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $chats = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Rol no válido.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Chats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px 0;
        }

        html, body {
            height: 100%;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100%;
        }

        .content {
            flex: 1;
        }

        .chat-link {
            text-decoration: none;
            color: inherit;
        }

        .chat-link:hover {
            background-color: #f8f9fa;
        }

        .chat-card {
            border: 1px solid #e3e6ea;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <?php include('../comp/header.php'); ?>

    <div class="content container mt-4">
        <h1 class="mb-4">Mis Chats</h1>

        <?php if (!empty($chats)): ?>
            <div class="list-group">
                <?php foreach ($chats as $chat): ?>
                    <a href="chat_cotizacion.php?idChat=<?= $chat['idChat'] ?>" class="chat-link">
                        <div class="chat-card p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?php if ($role === 'cliente'): ?>
                                        Con: <?= htmlspecialchars($chat['vendedor_nombre']) ?>
                                    <?php else: ?>
                                        Con: <?= htmlspecialchars($chat['cliente_nombre']) ?>
                                    <?php endif; ?>
                                </h5>
                                <small class="text-muted">
                                    Creado: <?= date("d/m/Y H:i", strtotime($chat['creado_en'])) ?>
                                </small>
                            </div>
                            <p class="mb-0 mt-2 text-muted">ID del Chat: <?= $chat['idChat'] ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                No tienes chats en este momento.
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <?php include('../comp/footer.php'); ?>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
