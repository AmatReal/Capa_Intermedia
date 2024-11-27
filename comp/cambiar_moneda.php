<?php
session_start();

if (isset($_POST['moneda'])) {
    $_SESSION['moneda'] = $_POST['moneda'];
}

// Redirige a la página anterior o a la página principal
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
