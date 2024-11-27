<?php
include('../views/database.php'); // Asegúrate de incluir la conexión a la base de datos

// Aprobar un producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aprobarProducto'])) {
    $idProducto = $_POST['idProducto'];
    /** @var mysqli $conn */
    // Actualizar el estado del producto a "aprobado"
    $sqlAprobar = "UPDATE productos SET aprobado = 1 WHERE idProducto = ?";
    $stmtAprobar = $conn->prepare($sqlAprobar);
    $stmtAprobar->bind_param("i", $idProducto);

    if ($stmtAprobar->execute()) {
        echo "<div class='alert alert-success' role='alert'>Producto aprobado con éxito!</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error al aprobar el producto.</div>";
    }

    $stmtAprobar->close();
}

// Obtener productos pendientes de aprobación
$sqlPendientes = "SELECT idProducto, nombre_producto, descripcion, precio FROM productos WHERE aprobado = 0";
$resultPendientes = $conn->query($sqlPendientes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="solicitudes.css">
    <title>Solicitudes de Productos</title>
</head>
<body>
    <?php include('../comp/header.php'); ?>

    <div class="container mt-4">
        <h2>Solicitudes de Productos Pendientes</h2>

        <?php if ($resultPendientes->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($producto = $resultPendientes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $producto['idProducto']; ?></td>
                                <td><?php echo $producto['nombre_producto']; ?></td>
                                <td><?php echo $producto['descripcion']; ?></td>
                                <td>$<?php echo $producto['precio']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="idProducto" value="<?php echo $producto['idProducto']; ?>">
                                        <button type="submit" name="aprobarProducto" class="btn btn-success btn-sm">Aprobar</button>
                                    </form>
                                    <a href="verProducto.php?id=<?php echo $producto['idProducto']; ?>" class="btn btn-info btn-sm">Ver detalles</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                No hay productos pendientes de aprobación.
            </div>
        <?php endif; ?>
    </div>

    <?php include('../comp/footer.php'); ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.11/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
