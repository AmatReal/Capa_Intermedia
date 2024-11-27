<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="explorar.css">
    <style>
        .btn-purple {
            background-color: #6f42c1;
            color: white;
        }
        .btn-purple:hover {
            background-color: #5a34a1;
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
    </style>
    <title>Explorar Productos</title>
    <?php include('../comp/header.php'); ?> 
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        
        <nav class="col-md-3 bg-dark text-white p-4">
            <h4>Filtros</h4>
            <form>
                <div class="form-group">
                    <label for="alfabeto">Alfabeto</label>
                    <select class="form-control" id="alfabeto">
                        <option value="">Selecciona</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="tema">Categoría</label>
                    <select class="form-control" id="categoria">
                        <option value="">Videojuegos</option>
                        <option value="Modelado">Comida</option>
                        <option value="Animación">Computadoras</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="marcas">Marcas</label>
                    <input type="text" class="form-control" id="marcas" placeholder="Buscar marcas">
                </div>
                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" class="form-control" id="precio" placeholder="Máximo precio">
                </div>
                <button type="submit" class="btn btn-purple">Filtrar</button>
            </form>
        </nav>

        <main class="col-md-9 p-4">
            <h2>Cursos Disponibles</h2>
            <div class="row">
                <?php
                include('../views/database.php'); // Asegúrate de incluir tu conexión a la base de datos
                /** @var mysqli $conn */
                // Obtener productos aprobados de la base de datos
                $sqlProductos = "SELECT idProducto, nombre_producto, precio 
                                 FROM productos 
                                 WHERE aprobado = 1"; // Solo productos aprobados
                $resultProductos = $conn->query($sqlProductos);

                if ($resultProductos->num_rows > 0) {
                    while ($producto = $resultProductos->fetch_assoc()) {
                        // Obtener la primera imagen asociada al producto desde la tabla multimedia
                        $sqlImagen = "SELECT archivo FROM multimedia WHERE id_producto = ? AND tipo = 'imagen' LIMIT 1";
                        $stmtImagen = $conn->prepare($sqlImagen);
                        $stmtImagen->bind_param("i", $producto['idProducto']);
                        $stmtImagen->execute();
                        $resultImagen = $stmtImagen->get_result();
                        $imagen = $resultImagen->fetch_assoc();

                        // Convertir la imagen a base64 si existe
                        $imagenSrc = '';
                        if ($imagen) {
                            $imagenSrc = 'data:image/jpeg;base64,' . base64_encode($imagen['archivo']);
                        } else {
                            // Imagen de reemplazo si no hay imagen en la base de datos
                            $imagenSrc = 'https://via.placeholder.com/150';
                        }

                        echo '
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <img src="' . $imagenSrc . '" class="card-img-top product-image" alt="' . $producto['nombre_producto'] . '">
                                <div class="card-body">
                                    <h5 class="card-title">' . $producto['nombre_producto'] . '</h5>
                                    <p class="card-text">Precio: $' . $producto['precio'] . '</p>
                                    <a href="producto.php?id=' . $producto['idProducto'] . '" class="btn btn-purple">Ver producto</a>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<p>No hay cursos disponibles.</p>';
                }
                ?>
            </div>
        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.11/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<?php include('../comp/footer.php'); ?>
</body>
</html>
