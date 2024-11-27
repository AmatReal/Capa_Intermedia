<?php
include('../comp/header.php');
include('../views/database.php'); // archivo de conexión a la base de datos

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del usuario de la sesión
$user_id = $_SESSION['user_id'];

// Obtener información completa del usuario y su avatar
$query = "SELECT u.full_name, u.username, u.email, u.birthdate, u.gender, u.role, a.archivo AS avatar
          FROM usuarios u
          LEFT JOIN avatares a ON u.idUser = a.idUserM
          WHERE u.idUser = ?";
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="text-primary mb-4">Perfil de Usuario</h1>
    <div class="row">
        <!-- Columna izquierda: Información del Usuario -->
        <div class="col-md-4">
            <div class="card">
                <?php if ($user['avatar']) : ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($user['avatar']); ?>" class="card-img-top" alt="Avatar del Usuario">
                <?php else : ?>
                    <img src="../img/default-avatar.jpg" class="card-img-top" alt="Avatar por Defecto">
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="card-title">Nombre completo: <?php echo htmlspecialchars($user['full_name']); ?></h5>
                    <p class="card-text"><strong>Nombre de usuario:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p class="card-text"><strong>Correo electrónico:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="card-text"><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($user['birthdate']); ?></p>
                    <p class="card-text"><strong>Género:</strong> <?php echo htmlspecialchars($user['gender']); ?></p>
                    <p class="card-text"><strong>Rol:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
                </div>
                <a href="editarperfil.php" class="btn btn-primary mt-3">Editar Perfil</a>
            </div>
        </div>

        <!-- Columna derecha: Creación y visualización de listas de deseos -->
        <div class="col-md-8">
            <!-- Formulario para Crear Lista de Deseos -->
            <div class="mb-4">
                <h2>Crear una nueva lista de deseos</h2>
                <form action="crear_lista_deseos.php" method="POST">
                    <div class="form-group mb-2">
                        <label for="nombre_lista">Nombre de la lista</label>
                        <input type="text" id="nombre_lista" name="nombre_lista" class="form-control" required>
                    </div>
                    <div class="form-group mb-2">
                        <label for="descripcion_lista">Descripción</label>
                        <textarea id="descripcion_lista" name="descripcion_lista" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-2">Crear lista</button>
                </form>
            </div>

            <!-- Mostrar las listas de deseos existentes -->
            <div>
                <h2>Mis Listas de Deseos</h2>
                <?php
                $query = "SELECT * FROM listas_deseos WHERE idUsuario = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($lista = $result->fetch_assoc()) {
                        echo "<div class='card mb-3'>
                                <div class='card-body'>
                                    <h5 class='card-title'>" . htmlspecialchars($lista['nombre_lista']) . "</h5>
                                    <p>" . htmlspecialchars($lista['descripcion']) . "</p>";

                        // Obtener los productos de esta lista
                        $queryProductos = "
                            SELECT p.idProducto, p.nombre_producto, p.precio 
                            FROM productos p
                            JOIN productos_lista pl ON p.idProducto = pl.idProducto
                            WHERE pl.idLista = ?";
                        $stmtProductos = $conn->prepare($queryProductos);
                        $stmtProductos->bind_param("i", $lista['idLista']);
                        $stmtProductos->execute();
                        $resultProductos = $stmtProductos->get_result();

                        // Mostrar los productos de cada lista de deseos
                        if ($resultProductos->num_rows > 0) {
                            echo "<h6>Productos en esta lista:</h6><ul class='list-group'>";
                            while ($producto = $resultProductos->fetch_assoc()) {
                                echo "<li class='list-group-item'>
                                        <strong>" . htmlspecialchars($producto['nombre_producto']) . "</strong> - $" . number_format($producto['precio'], 2) . "
                                        <a href='producto.php?id=" . $producto['idProducto'] . "' class='btn btn-info btn-sm float-end ms-2'>Ver Producto</a>
                                      </li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>No hay productos en esta lista.</p>";
                        }

                        echo "</div></div>";
                    }
                } else {
                    echo "<p>No tienes listas de deseos creadas.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
