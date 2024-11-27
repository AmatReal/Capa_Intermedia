<?php
require 'database.php'; // Conexión a la base de datos
session_start();
/** @var mysqli $conn */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Preparar consulta SQL
    $stmt = mysqli_prepare($conn, "SELECT idUser, username, email, avatar, status, role FROM usuarios WHERE email = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $email, $password); // 'ss' indica que ambos parámetros son strings

    // Ejecutar la consulta
    mysqli_stmt_execute($stmt);

    // Obtener resultados
    $result = mysqli_stmt_get_result($stmt);

    // Verificar si las credenciales son correctas
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if ($user['status'] == 0) {
            echo "<script>alert('Usuario deshabilitado! Favor de contactar un administrador'); window.location.href = 'login.php';</script>";
        } 
        else {
            // Guardar información del usuario en la sesión
            $_SESSION['user_id'] = $user['idUser'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['avatar'] = $user['avatar'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'vendedor') {
                $_SESSION['es_vendedor'] = true;
            } else {
                $_SESSION['es_vendedor'] = false;
            }

            // Redirigir al menú principal
            header("Location: ../views/menu.php");
            exit();
        }
    } 
    else {
        // Si las credenciales no son válidas
        echo "<script>alert('Correo o contraseña incorrectos'); window.location.href = 'login.php';</script>";
    }

    // Cerrar la declaración
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Peaceframe Market</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center text-primary">Iniciar Sesión</h2>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                </form>
                <p class="text-center mt-3">¿No tienes una cuenta? <a href="../views/register.php">Regístrate aquí</a></p>
            </div>
        </div>
    </div>
    <?php include('../comp/footer.php'); ?>
</body>

</html>