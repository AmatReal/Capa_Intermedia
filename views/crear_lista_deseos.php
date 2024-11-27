<?php
include('../views/database.php');
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $nombre_lista = $_POST['nombre_lista'];
    $descripcion_lista = $_POST['descripcion_lista'];

    // Insertar nueva lista de deseos
    $sql = "INSERT INTO listas_deseos (idUsuario, nombre_lista, descripcion) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_id, $nombre_lista, $descripcion_lista);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Lista de deseos creada con éxito.</div>";
        header("Location: perfil.php");
    } else {
        echo "<div class='alert alert-danger'>Error al crear la lista.</div>";
    }
}
?>
