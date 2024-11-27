<?php
include('../views/database.php');
session_start();

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener los datos del formulario
$idProducto = intval($_POST['idProducto']);
$idLista = intval($_POST['idLista']);

// Comprobar si la relación ya existe en la tabla productos_lista
$sqlCheck = "SELECT * FROM productos_lista WHERE idProducto = ? AND idLista = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $idProducto, $idLista);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows == 0) {
    // Insertar la relación en productos_lista
    $sqlInsert = "INSERT INTO productos_lista (idProducto, idLista) VALUES (?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ii", $idProducto, $idLista);
    $stmtInsert->execute();

    echo "<div class='alert alert-success'>Producto añadido a la lista de deseos con éxito.</div>";
} else {
    echo "<div class='alert alert-warning'>Este producto ya está en la lista de deseos.</div>";
}

// Redirigir de vuelta al producto
header("Location: producto.php?id=$idProducto");
exit;
?>
