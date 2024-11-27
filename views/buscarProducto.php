<?php
include('../views/database.php'); // Asegúrate de conectar a la base de datos

if (isset($_POST['search'])) {
    $search = $conn->real_escape_string($_POST['search']);
    $sql = "SELECT idProducto, nombre_producto FROM productos WHERE nombre_producto LIKE '%$search%' LIMIT 5";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($producto = $result->fetch_assoc()) {
            echo '<div class="dropdown-search-item" data-id="' . $producto['idProducto'] . '">' . $producto['nombre_producto'] . '</div>';
        }
    } else {
        echo '<div class="dropdown-search-item">No se encontraron productos</div>';
    }
}
?>
