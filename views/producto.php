<?php 
include('../views/database.php');
session_start();
/** @var mysqli $conn */
// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    
    exit;
}

// Obtener el ID del producto desde la URL
if (isset($_GET['id'])) {
    $productoId = intval($_GET['id']);  // Convertir a entero por seguridad

    // Consulta para obtener detalles del producto
    $sqlProducto = "SELECT * FROM productos WHERE idProducto = ?";
    $stmt = $conn->prepare($sqlProducto);
    $stmt->bind_param("i", $productoId);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();

    if (!$producto) {
        echo "<h2>Producto no encontrado</h2>";
        exit;
    }

    // Consultar el nombre de la categoría
    $sqlCategoria = "SELECT nombre_categoria FROM categorias WHERE idCat = ?";
    $stmtCategoria = $conn->prepare($sqlCategoria);
    $stmtCategoria->bind_param("i", $producto['id_categoria']);
    $stmtCategoria->execute();
    $resultCategoria = $stmtCategoria->get_result();
    $categoria = $resultCategoria->fetch_assoc();

    // Consultar imágenes del producto
    $sqlImagenes = "SELECT archivo FROM multimedia WHERE id_producto = ? AND tipo = 'imagen'";
    $stmtImagenes = $conn->prepare($sqlImagenes);
    $stmtImagenes->bind_param("i", $productoId);
    $stmtImagenes->execute();
    $resultImagenes = $stmtImagenes->get_result();

    $imagenes = [];
    while ($row = $resultImagenes->fetch_assoc()) {
        $imagenes[] = $row['archivo'];
    }

    // Consultar video del producto
    $sqlVideo = "SELECT archivo FROM multimedia WHERE id_producto = ? AND tipo = 'video'";
    $stmtVideo = $conn->prepare($sqlVideo);
    $stmtVideo->bind_param("i", $productoId);
    $stmtVideo->execute();
    $resultVideo = $stmtVideo->get_result();

    $video = null;
    if ($resultVideo->num_rows > 0) {
        $video = $resultVideo->fetch_assoc()['archivo'];
    }

    // Revisar si el producto es de tipo "cotización"
    $esCotizacion = $producto['tipo_venta'] === 'cotizar';


    // Obtener el vendedor del producto
    $sqlVendedor = "SELECT idUser FROM usuarios WHERE idUser = ?";
    $stmtVendedor = $conn->prepare($sqlVendedor);
    $stmtVendedor->bind_param("i", $producto['id_vendedor']);
    $stmtVendedor->execute();
    $resultVendedor = $stmtVendedor->get_result();
    $vendedor = $resultVendedor->fetch_assoc();

    // Calcular el promedio de valoraciones
    $sqlPromedio = "SELECT AVG(valoracion) AS promedio, COUNT(valoracion) AS total FROM valoraciones WHERE idProducto = ?";
    $stmtPromedio = $conn->prepare($sqlPromedio);
    $stmtPromedio->bind_param("i", $productoId);
    $stmtPromedio->execute();
    $resultPromedio = $stmtPromedio->get_result();
    $promedio = $resultPromedio->fetch_assoc();
    $promedioEstrellas = $promedio['promedio'] ? round($promedio['promedio'], 1) : 0;
    $totalValoraciones = $promedio['total'];

    // Verificar si el usuario ha comprado el producto
    $user_id = $_SESSION['user_id'];
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

    // Consultar niveles del curso
    $sqlNiveles = "SELECT id_nivel, nombre, descripcion FROM niveles WHERE id_producto = ? AND status = 1";
    $stmtNiveles = $conn->prepare($sqlNiveles);
    $stmtNiveles->bind_param("i", $productoId);
    $stmtNiveles->execute();
    $resultNiveles = $stmtNiveles->get_result();

    $niveles = [];
    while ($row = $resultNiveles->fetch_assoc()) {
        $niveles[] = $row;
    }

    } else {
        echo "<div class='alert alert-danger'>No se ha especificado un producto.</div>";
        exit;
    }

    // Obtener las listas de deseos del usuario
    $user_id = $_SESSION['user_id'];
    $sqlListas = "SELECT * FROM listas_deseos WHERE idUsuario = ?";
    $stmtListas = $conn->prepare($sqlListas);
    $stmtListas->bind_param("i", $user_id);
    $stmtListas->execute();
    $resultListas = $stmtListas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre_producto']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/producto.css">
</head>
<body>
<?php include('../comp/header.php'); ?>

<div class="container mt-5">
    <div class="row">
        <!-- Columna Izquierda: Nombre, Calificación e Imágenes -->
        <div class="col-md-8">
            <h1 class="product-name"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h1>
            <p class="rating">Calificación promedio: <?php echo $promedioEstrellas; ?> ⭐ (<?php echo $totalValoraciones; ?> valoraciones)</p>
                <!-- Formulario para valorar el producto -->
                <div class="mt-4">
                <h3>Valorar el Producto</h3>
                <?php if ($compra['comprado'] > 0): ?>
                    <?php if (!empty($niveles)): ?>
                        <div class="col-md-12 mt-4">
                            <h3>Selecciona un Nivel</h3>
                            <div class="list-group">
                                <?php foreach ($niveles as $nivel): ?>
                                    <button 
                                        class="list-group-item list-group-item-action nivel-btn" 
                                        data-id="<?php echo $nivel['id_nivel']; ?>">
                                        <?php echo htmlspecialchars($nivel['nombre']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Contenedor donde se actualizarán los detalles del nivel -->
                        <div id="nivel-detalles" class="mt-4">
                            <h3>Detalles del Nivel</h3>
                            <p>Selecciona un nivel para ver sus detalles.</p>
                        </div>
                    <?php else: ?>
                        <p>No hay niveles disponibles para este curso.</p>
                    <?php endif; ?>

                    <form action="../views/procesar_valoracion.php" method="POST">
                        <label for="valoracion">Selecciona tu valoración:</label>
                        <select name="valoracion" id="valoracion" class="form-control" required>
                            <option value="5">⭐ ⭐ ⭐ ⭐ ⭐ (Excelente)</option>
                            <option value="4">⭐ ⭐ ⭐ ⭐ (Muy bueno)</option>
                            <option value="3">⭐ ⭐ ⭐ (Bueno)</option>
                            <option value="2">⭐ ⭐ (Regular)</option>
                            <option value="1">⭐ (Malo)</option>
                        </select>
                        <input type="hidden" name="idProducto" value="<?php echo $productoId; ?>">
                        <button type="submit" class="btn btn-primary mt-2">Enviar valoración</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Solo los usuarios que han comprado este producto pueden valorarlo.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Imagen Principal -->
            <div class="product-image-container">
                <?php if (!empty($imagenes)): ?>
                    <img id="mainImage" src="data:image/jpeg;base64,<?php echo base64_encode($imagenes[0]); ?>" alt="Imagen del Producto" class="img-fluid main-image">
                <?php endif; ?>
            </div>

            <!-- Carousel de Imágenes -->
            <div id="productCarousel" class="carousel">
                <div class="row mt-3">
                    <?php foreach ($imagenes as $imagen): ?>
                        <div class="col">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($imagen); ?>" class="img-thumbnail carousel-img" onclick="changeImage(this)">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mt-4">
                <h5>Categoría: <?php echo htmlspecialchars($categoria['nombre_categoria']); ?></h5>
            </div>

            <div class="mt-4">
                <h3>Descripción del Producto</h3>
                <p class="product-description"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
            </div>
            <!-- Niveles del Curso -->
            <div class="mt-4">
                <h3>Niveles del Curso</h3>
                <?php if (!empty($niveles)): ?>
                    <ul class="list-group">
                        <?php foreach ($niveles as $nivel): ?>
                            <li class="list-group-item">
                                <h5><?php echo htmlspecialchars($nivel['nombre']); ?></h5>
                                <p><?php echo htmlspecialchars($nivel['descripcion']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No hay niveles disponibles para este curso.</p>
                <?php endif; ?>
            </div>

            <!-- Video del Producto -->
            <?php if ($video): ?>
                <div class="mt-4">
                    <h3>Video del Producto</h3>
                    <video width="320" height="240" controls>
                        <source src="data:video/mp4;base64,<?php echo base64_encode($video); ?>" type="video/mp4">
                        Tu navegador no soporta el elemento de video.
                    </video>
                </div>
            <?php endif; ?>
            <!-- Sección de Comentarios -->
            <!-- Sección de Comentarios -->
<div class="mt-4">
    <?php include('../views/comentarios.php'); ?>
</div>
    <?php if ($compra['comprado'] <= 0): ?>
        </div>
            <!-- Columna Derecha: Precio y Botón de Comprar -->
            <div class="col-md-4 text-center">
                <div class="price-container">
                    <?php if ($esCotizacion): ?>
                        <!-- Botón para cotizar -->
                        <form action="iniciar_chat.php" method="POST">
                            <input type="hidden" name="idProducto" value="<?php echo $productoId; ?>">
                            <input type="hidden" name="idVendedor" value="<?php echo $producto['id_vendedor']; ?>">
                            <button type="submit" class="btn btn-primary btn-lg mt-3">Cotizar</button>
                        </form>
                    <?php else: ?>
                                <h2 class="price">$<?php echo number_format($producto['precio'], 2); ?></h2>
                    
                    <!-- Formulario para agregar al carrito -->
                    <form action="../views/agregar_carrito.php" method="POST">
                        <input type="hidden" name="idProducto" value="<?php echo $productoId; ?>">
                        <input type="hidden" name="nombreProducto" value="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                        <input type="hidden" name="precio" value="<?php echo $producto['precio']; ?>">
                        <div class="form-group">
                            <label for="cantidad">Cantidad:</label>
                            <input type="number" name="cantidad" min="1" max="<?php echo htmlspecialchars($producto['cantidad_disponible']); ?>" value="1" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg mt-3">Añadir al Carrito</button>
                    </form>
                            <?php endif; ?>

                    

                    <!-- Formulario para agregar a la lista de deseos -->
                    <div class="mt-4">
                        <h4>Añadir a la Lista de Deseos</h4>
                        <form action="agregar_a_lista.php" method="POST">
                            <div class="form-group">
                                <label for="idLista">Selecciona una lista de deseos</label>
                                <select name="idLista" id="idLista" class="form-control" required>
                                    <?php while ($lista = $resultListas->fetch_assoc()): ?>
                                        <option value="<?php echo $lista['idLista']; ?>"><?php echo htmlspecialchars($lista['nombre_lista']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <input type="hidden" name="idProducto" value="<?php echo $productoId; ?>">
                            <button type="submit" class="btn btn-warning mt-3">Añadir a la Lista</button>
                        </form>
                    </div>
                    
                    <p class="product-quantity">
                        Productos en existencia: <?php echo htmlspecialchars($producto['cantidad_disponible']); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Función para cambiar la imagen principal del producto
    function changeImage(element) {
        document.getElementById('mainImage').src = element.src;
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Agregar evento click a los botones de nivel
        document.querySelectorAll('.nivel-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const nivelId = this.getAttribute('data-id');
                
                // Realizar la solicitud AJAX
                fetch(`nivel_detalles.php?id=${nivelId}`)
                    .then(response => response.json())
                    .then(data => {
                        const contenedor = document.getElementById('nivel-detalles');
                        if (data.success) {
                            contenedor.innerHTML = `
                                <h3>${data.nombre}</h3>
                                <p>${data.descripcion}</p>
                                <video controls src="data:video/mp4;base64,${data.video}" class="mt-3" style="width: 100%;"></video>
                                <a href="data:application/pdf;base64,${data.archivo}" class="btn btn-primary mt-3" download="Nivel-${data.nombre}.pdf">Descargar PDF</a>
                            `;
                        } else {
                            contenedor.innerHTML = `<p>Error al cargar los detalles del nivel.</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('nivel-detalles').innerHTML = `<p>Ocurrió un error al cargar el nivel.</p>`;
                    });
            });
        });
    });
</script>


<?php include('../comp/footer.php'); ?>
</body>
</html>
