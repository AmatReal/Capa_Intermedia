<?php
include('../views/database.php');


$user_id = $_SESSION['user_id'];
$productoId = intval($_GET['id']); // ID del producto desde la URL

// Verificar si el usuario ha comprado el producto
$sqlCompra = "
    SELECT COUNT(*) AS comprado
    FROM ventas v
    INNER JOIN pedidos p ON v.idPedido = p.idPedido
    WHERE p.idCliente = ? AND v.idProducto = ?";
$stmtCompra = $conn->prepare($sqlCompra);
$stmtCompra->bind_param("ii", $user_id, $productoId);
$stmtCompra->execute();
$resultCompra = $stmtCompra->get_result();
$compra = $resultCompra->fetch_assoc();

// Insertar comentario si el formulario es enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comentario'])) {
    $comentario = trim($_POST['comentario']);

    if (!empty($comentario)) {
        $sqlInsertComentario = "INSERT INTO comentarios (idProducto, idUsuario, comentario) VALUES (?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsertComentario);
        $stmtInsert->bind_param("iis", $productoId, $user_id, $comentario);
        $stmtInsert->execute();
        $stmtInsert->close();
    }
}

// Consultar comentarios del producto
$sqlComentarios = "
    SELECT c.comentario, c.fecha, u.username AS usuario
    FROM comentarios c
    INNER JOIN usuarios u ON c.idUsuario = u.idUser
    WHERE c.idProducto = ?
    ORDER BY c.fecha DESC";
$stmtComentarios = $conn->prepare($sqlComentarios);
$stmtComentarios->bind_param("i", $productoId);
$stmtComentarios->execute();
$resultComentarios = $stmtComentarios->get_result();
?>

<div class="mt-4">
    <h3>Comentarios</h3>

    <!-- Mostrar formulario para comentar si el usuario ha comprado el producto -->
    <?php if ($compra['comprado'] > 0): ?>
        <form action="" method="POST" class="mb-4">
            <div class="form-group">
                <label for="comentario">Escribe tu comentario:</label>
                <textarea name="comentario" id="comentario" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Enviar comentario</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">
            Solo los usuarios que han comprado este producto pueden escribir un comentario.
        </div>
    <?php endif; ?>

    <!-- Mostrar comentarios existentes -->
    <?php if ($resultComentarios->num_rows > 0): ?>
        <ul class="list-group">
            <?php while ($comentario = $resultComentarios->fetch_assoc()): ?>
                <li class="list-group-item">
                    <strong><?php echo htmlspecialchars($comentario['usuario']); ?></strong> 
                    <small class="text-muted">(<?php echo $comentario['fecha']; ?>)</small>
                    <p><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-info">
            Aún no hay comentarios para este producto.
        </div>
    <?php endif; ?>
</div>
