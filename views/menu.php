<?php
include('../comp/header.php');
include('../views/database.php'); // archivo de conexión a la base de datos

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
/** @var mysqli $conn */
// Obtener información del usuario en sesión
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, avatar FROM usuarios WHERE idUser = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "No se encontró la información del usuario.";
    exit;
}

// Verificar si el usuario tiene un avatar y obtener la información de la imagen desde la tabla avatares
$avatarId = $user['avatar'];
if ($avatarId) {
    $avatarQuery = "SELECT archivo, tipo FROM avatares WHERE idUserM = ?";
    $avatarStmt = $conn->prepare($avatarQuery);
    $avatarStmt->bind_param("i", $user_id);
    $avatarStmt->execute();
    $avatarResult = $avatarStmt->get_result();

    if ($avatarResult->num_rows > 0) {
        $avatarData = $avatarResult->fetch_assoc();
        $avatarFile = $avatarData['archivo'];
        $avatarType = $avatarData['tipo'];
    } else {
        $avatarFile = null;
        $avatarType = null;
    }
}

// Obtener los productos más vendidos basados en la tabla de ventas
$sqlMasVendidos = "
    SELECT * FROM vista_mas_vendidos;";
$resultMasVendidos = $conn->query($sqlMasVendidos);

// Obtener los productos más nuevos basados en `idProducto` (asume que IDs más altos son más recientes)
$sqlMasNuevos = "
    SELECT * FROM vista_mas_nuevos;";
$resultMasNuevos = $conn->query($sqlMasNuevos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>

<div class="container mt-4">
    <div class="row">
        <!-- Panel de información del usuario -->
        <div class="col-md-3">
            <div class="card mb-4 shadow-sm">
                <?php if ($avatarFile): ?>
                    <img src="data:<?php echo htmlspecialchars($avatarType); ?>;base64,<?php echo base64_encode($avatarFile); ?>" class="card-img-top" alt="Avatar del Usuario">
                <?php else: ?>
                    <img src="../img/default-avatar.png" class="card-img-top" alt="Avatar del Usuario">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
        </div>

        <!-- Carrusel de productos más vendidos -->
        <div class="col-md-9">
            <h1 class="text-primary">Lo más vendido</h1>
            <div id="masVendidosCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php
                    $vendidosChunk = array_chunk($resultMasVendidos->fetch_all(MYSQLI_ASSOC), 4);
                    foreach ($vendidosChunk as $index => $productos):
                    ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="row">
                                <?php foreach ($productos as $producto): ?>
                                    <div class="col-md-3">
                                        <div class="card mb-4 shadow-sm">
                                            <img src="<?php echo $producto['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($producto['imagen']) : 'https://via.placeholder.com/150'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h5>
                                                <p class="card-text">$<?php echo number_format($producto['precio'], 2); ?></p>
                                                <a href="../views/producto.php?id=<?php echo $producto['idProducto']; ?>" class="btn btn-primary">Ver más</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#masVendidosCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#masVendidosCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>

            <!-- Carrusel de productos más nuevos -->
            <h2 class="text-primary mt-5">Lo más nuevo</h2>
            <div id="masNuevosCarousel" class="carousel slide" data-bs-interval="false">
    <div class="carousel-inner">
        <?php
        $nuevosChunk = array_chunk($resultMasNuevos->fetch_all(MYSQLI_ASSOC), 4);
        foreach ($nuevosChunk as $index => $productos):
        ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="row">
                    <?php foreach ($productos as $producto): ?>
                        <div class="col-md-3">
                            <div class="card mb-4 shadow-sm">
                                <img src="<?php echo $producto['imagen'] ? 'data:image/jpeg;base64,' . base64_encode($producto['imagen']) : 'https://via.placeholder.com/150'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h5>
                                    <p class="card-text">$<?php echo number_format($producto['precio'], 2); ?></p>
                                    <a href="../views/producto.php?id=<?php echo $producto['idProducto']; ?>" class="btn btn-primary">Ver más</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#masNuevosCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#masNuevosCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include('../comp/footer.php'); ?>
</body>
</html>
