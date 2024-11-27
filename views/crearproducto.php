<?php
include('../views/database.php');
session_start();
/** @var mysqli $conn */
// Cargar categorías desde la base de datos
$categorias = [];
$sqlCategorias = "SELECT idCat, nombre_categoria FROM categorias";
$resultCategorias = $conn->query($sqlCategorias);

if ($resultCategorias->num_rows > 0) {
    while ($row = $resultCategorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Inicializar mensaje
$mensaje = "";
$mensajeTipo = "";

// Manejo del formulario al enviar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errores = [];
    $nombreProducto = $_POST['nombreProducto'];
    $descripcionProducto = $_POST['descripcionProducto'];
    $categoriaProducto = $_POST['categoriaProducto'];
    $tipoVenta = $_POST['tipoVenta'];
    $precioProducto = ($tipoVenta === "vender") ? $_POST['precioProducto'] : null; // Precio solo si es "vender"
    $cantidadProducto = $_POST['cantidadProducto'];
    $user_id = $_SESSION['user_id'];

    // Validaciones del servidor
    if (!preg_match("/^[a-zA-Z0-9\s]+$/", $nombreProducto)) {
        $errores[] = "El nombre del curso solo puede contener letras, números y espacios.";
    }

    if (!preg_match("/^[a-zA-Z0-9\s.,]+$/", $descripcionProducto)) {
        $errores[] = "La descripción solo puede contener letras, números, espacios, comas y puntos.";
    }

    foreach ($_FILES['imagenesProducto']['tmp_name'] as $tmpName) {
        if (is_uploaded_file($tmpName)) {
            $fileType = mime_content_type($tmpName);
            if (!in_array($fileType, ['image/png', 'image/jpeg'])) {
                $errores[] = "Las imágenes deben ser en formato PNG o JPG.";
            }
        }
    }

    if (is_uploaded_file($_FILES['videoProducto']['tmp_name'])) {
        $fileTypeVideo = mime_content_type($_FILES['videoProducto']['tmp_name']);
        if (strpos($fileTypeVideo, 'video/') !== 0) {
            $errores[] = "El archivo del video debe ser un archivo de video válido.";
        }
    }

    if (!empty($errores)) {
        $mensaje = implode("<br>", $errores);
        $mensajeTipo = "danger";
    } else {
        // Verificar si se ha seleccionado o ingresado una nueva categoría
        if (!empty($_POST['nuevaCategoria'])) {
            $nuevaCategoria = $_POST['nuevaCategoria'];

            // Verificar si la categoría ya existe
            $sqlVerificarCategoria = "SELECT idCat FROM categorias WHERE nombre_categoria = ?";
            $stmtVerificar = $conn->prepare($sqlVerificarCategoria);
            $stmtVerificar->bind_param("s", $nuevaCategoria);
            $stmtVerificar->execute();
            $stmtVerificar->store_result();

            if ($stmtVerificar->num_rows == 0) {
                // Insertar nueva categoría
                $sqlInsertCategoria = "INSERT INTO categorias (nombre_categoria) VALUES (?)";
                $stmtInsertar = $conn->prepare($sqlInsertCategoria);
                $stmtInsertar->bind_param("s", $nuevaCategoria);
                $stmtInsertar->execute();
                $categoriaProducto = $conn->insert_id; // ID de la nueva categoría
            } else {
                // Obtener el ID de la categoría existente
                $stmtVerificar->bind_result($categoriaProducto);
                $stmtVerificar->fetch();
            }
            $stmtVerificar->close();
        }

        // Insertar en la tabla de productos
        $sqlProducto = "INSERT INTO productos (nombre_producto, descripcion, id_categoria, tipo_venta, precio, cantidad_disponible, id_vendedor) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlProducto);
        $stmt->bind_param("ssissii", $nombreProducto, $descripcionProducto, $categoriaProducto, $tipoVenta, $precioProducto, $cantidadProducto, $user_id);
        $stmt->execute();

        // Obtener el ID del producto recién creado
        $productoId = $conn->insert_id;

        // Procesar archivos de imágenes
        foreach ($_FILES['imagenesProducto']['tmp_name'] as $key => $tmpName) {
            if (is_uploaded_file($tmpName)) {
                $fileData = file_get_contents($tmpName);
                $fileType = 'imagen';
                $sqlMultimedia = "INSERT INTO multimedia (tipo, archivo, id_producto) VALUES (?, ?, ?)";
                $stmtMultimedia = $conn->prepare($sqlMultimedia);
                $stmtMultimedia->bind_param("sbi", $fileType, $null, $productoId);
                $stmtMultimedia->send_long_data(1, $fileData);
                $stmtMultimedia->execute();
            }
        }

        // Procesar archivo de video
        $tmpNameVideo = $_FILES['videoProducto']['tmp_name'];
        if (is_uploaded_file($tmpNameVideo)) {
            $fileDataVideo = file_get_contents($tmpNameVideo);
            $fileTypeVideo = 'video';

            $sqlMultimedia = "INSERT INTO multimedia (tipo, archivo, id_producto) VALUES (?, ?, ?)";
            $stmtVideo = $conn->prepare($sqlMultimedia);
            $stmtVideo->bind_param("sbi", $fileTypeVideo, $null, $productoId);
            $stmtVideo->send_long_data(1, $fileDataVideo);
            $stmtVideo->execute();
        }

        $mensaje = "Producto creado exitosamente.";
        $mensajeTipo = "success";
    }

    // Después de guardar el producto, procesar los niveles
    if (isset($_POST['nombreNivel']) && is_array($_POST['nombreNivel'])) {
        $niveles = $_POST['nombreNivel'];
        $descripciones = $_POST['descripcionNivel'];
        $pdfs = $_FILES['pdfNivel'];
        $videos = $_FILES['videoNivel'];

        foreach ($niveles as $index => $nivel) {
            $nombreNivel = $nivel;
            $descripcionNivel = $descripciones[$index];

            // Manejar el archivo PDF
            $pdfData = null;
            if (isset($pdfs['tmp_name'][$index]) && is_uploaded_file($pdfs['tmp_name'][$index])) {
                $pdfData = file_get_contents($pdfs['tmp_name'][$index]);
            }

            // Manejar el archivo de video
            $videoData = null;
            if (isset($videos['tmp_name'][$index]) && is_uploaded_file($videos['tmp_name'][$index])) {
                $videoData = file_get_contents($videos['tmp_name'][$index]);
            }

            // Insertar en la tabla de niveles
            $sqlNivel = "INSERT INTO niveles (nombre, descripcion, archivo, video, id_producto) VALUES (?, ?, ?, ?, ?)";
            $stmtNivel = $conn->prepare($sqlNivel);
            $stmtNivel->bind_param("ssbbi", $nombreNivel, $descripcionNivel, $null, $null, $productoId);

            // Subir los archivos si existen
            if ($pdfData) {
                $stmtNivel->send_long_data(2, $pdfData);
            }
            if ($videoData) {
                $stmtNivel->send_long_data(3, $videoData);
            }

            $stmtNivel->execute();
        }
    }

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include('../comp/header.php'); ?>

<div class="container mt-5">
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $mensajeTipo; ?>" role="alert">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nombreProducto">Nombre del Curso</label>
            <input type="text" name="nombreProducto" class="form-control" id="nombreProducto" placeholder="Nombre del curso" required>
        </div>
        <div class="form-group">
            <label for="descripcionProducto">Descripción</label>
            <textarea name="descripcionProducto" class="form-control" id="descripcionProducto" rows="3" placeholder="Descripción del curso" required></textarea>
        </div>
        <div class="form-group">
            <label for="imagenesProducto">Imágenes del Curso (mínimo 3)</label>
            <input type="file" name="imagenesProducto[]" class="form-control-file" id="imagenesProducto" multiple required>
        </div>
        <div class="form-group">
            <label for="videoProducto">Video del Curso (mínimo 1)</label>
            <input type="file" name="videoProducto" class="form-control-file" id="videoProducto" accept="video/*" required>
        </div>
        <div class="form-group">
            <label for="categoriaProducto">Categoría</label>
            <select name="categoriaProducto" class="form-control" id="categoriaProducto" required>
                <option value="">Selecciona una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['idCat']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="nuevaCategoria">Nueva Categoría</label>
            <div class="input-group">
                <input type="text" id="nuevaCategoria" class="form-control" placeholder="Ingresa una nueva categoría">
                <button type="button" id="addCategoryButton" class="btn btn-secondary">Añadir</button>
            </div>
            <small id="categoryFeedback" class="form-text"></small>
        </div>
        <div class="form-group">
            <label for="tipoVenta">¿Es para vender o gratuito?</label>
            <select name="tipoVenta" class="form-control" id="tipoVenta" required>
                <option value="
                ">Vender</option>
                <option value="cotizar">Gratis</option>
            </select>
        </div>
        <div class="form-group" id="precioSection" style="display:none;">
            <label for="precioProducto">Precio</label>
            <input type="number" name="precioProducto" class="form-control" id="precioProducto" placeholder="Precio del producto">
        </div>
        <div class="form-group">
            <label for="cantidadProducto">Cantidad Disponible</label>
            <input type="number" name="cantidadProducto" class="form-control" id="cantidadProducto" placeholder="Cantidad disponible" required>
        </div>

        <!-- Sección para agregar niveles -->
        <div class="form-group">
            <label for="nivelesProducto">Niveles del Curso</label>
            <button type="button" id="addNivelButton" class="btn btn-secondary">Agregar Nivel</button>
            <div id="nivelesProductoContainer"></div> <!-- Aquí se agregarán los niveles -->
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-primary">Crear Producto</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('tipoVenta').addEventListener('change', function () {
        var precioSection = document.getElementById('precioSection');
        precioSection.style.display = (this.value === 'vender') ? 'block' : 'none';
    });

    document.getElementById('addCategoryButton').addEventListener('click', function () {
        const nuevaCategoria = document.getElementById('nuevaCategoria').value.trim();
        const feedback = document.getElementById('categoryFeedback');

        if (nuevaCategoria === "") {
            feedback.textContent = "Por favor, ingresa un nombre para la categoría.";
            feedback.style.color = "red";
            return;
        }

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "add_category.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);

                    if (response.status === "success") {
                        const categoriaProducto = document.getElementById('categoriaProducto');
                        const nuevaOpcion = document.createElement('option');
                        nuevaOpcion.value = response.idCat;
                        nuevaOpcion.textContent = response.nombre_categoria;
                        categoriaProducto.appendChild(nuevaOpcion);
                        categoriaProducto.value = response.idCat;
                        feedback.textContent = "Categoría añadida exitosamente.";
                        feedback.style.color = "green";
                        document.getElementById('nuevaCategoria').value = "";
                    } else {
                        feedback.textContent = response.message;
                        feedback.style.color = "red";
                    }
                } else {
                    feedback.textContent = "Error en la solicitud. Intenta nuevamente.";
                    feedback.style.color = "red";
                }
            }
        };

        xhr.send(`nuevaCategoria=${encodeURIComponent(nuevaCategoria)}`);
    });

    document.getElementById('addNivelButton').addEventListener('click', function () {
    // Crear un nuevo contenedor de nivel
    const contenedorNiveles = document.getElementById('nivelesProductoContainer');
    const nuevoNivel = document.createElement('div');
    nuevoNivel.classList.add('nivel-container');

    // Contenido del nivel
    nuevoNivel.innerHTML = `
        <hr>
        <div class="form-group">
            <label for="nombreNivel">Título del Nivel</label>
            <input type="text" name="nombreNivel[]" class="form-control" placeholder="Título del nivel" required>
        </div>
        <div class="form-group">
            <label for="descripcionNivel">Descripción</label>
            <textarea name="descripcionNivel[]" class="form-control" rows="3" placeholder="Descripción del nivel" required></textarea>
        </div>
        <div class="form-group">
            <label for="pdfNivel">Archivo PDF</label>
            <input type="file" name="pdfNivel[]" class="form-control-file" accept="application/pdf">
        </div>
        <div class="form-group">
            <label for="videoNivel">Video</label>
            <input type="file" name="videoNivel[]" class="form-control-file" accept="video/*">
        </div>
    `;
    
    // Agregar el nuevo nivel al contenedor
    contenedorNiveles.appendChild(nuevoNivel);
});

</script>

<?php include('../comp/footer.php'); ?>
</body>
</html>
