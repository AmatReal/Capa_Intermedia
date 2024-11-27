<?php

include('../comp/header.php');
include('../views/database.php'); // archivo de conexión a la base de datos

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos enviados desde el formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = !empty($_POST['password']) ? $_POST['password'] : null;
    $avatar = $_FILES['avatar'];

    // Validar y actualizar datos
    if (!empty($username) && !empty($email)) {
        $query = "UPDATE usuarios SET username = ?, email = ? WHERE idUser = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $username, $email, $user_id);

        // Si el usuario proporcionó una nueva contraseña, actualizarla también
        if (!empty($password)) {
            $query_password = "UPDATE usuarios SET password = ? WHERE idUser = ?";
            $stmt_password = $conn->prepare($query_password);
            $stmt_password->bind_param("si", $password, $user_id);
            $stmt_password->execute();
        }

        // Manejar la subida de avatar si el usuario seleccionó uno
        if ($avatar['error'] === 0) {
            $avatarData = file_get_contents($avatar['tmp_name']);
            $avatarTipo = $avatar['type'];

            $query_avatar = "UPDATE avatares SET archivo = ?, tipo = ? WHERE idUserM = ?";
            $stmt_avatar = $conn->prepare($query_avatar);
            $stmt_avatar->bind_param("bsi", $avatarData, $avatarTipo, $user_id);
            $stmt_avatar->send_long_data(0, $avatarData);
            $stmt_avatar->execute();
        }

        // Ejecutar la actualización de datos básicos
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Perfil actualizado exitosamente.</div>";
        } else {
            echo "<div class='alert alert-danger'>Ocurrió un error al actualizar el perfil.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Por favor, completa todos los campos requeridos.</div>";
    }
}

// Obtener la información actual del usuario
$query = "SELECT u.username, u.email, a.archivo AS avatar FROM usuarios u 
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
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-left mb-4">Editar perfil</h2>
                <form method="POST" enctype="multipart/form-data">

                    <div class="form-group">
                        <label for="username">Nombre de Usuario:</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" minlength="3" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Nueva Contraseña (opcional):</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="avatar">Imagen de Avatar:</label>
                        <input type="file" class="form-control-file" id="avatar" name="avatar" accept="image/*">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($user['avatar']); ?>" alt="Avatar actual" class="img-thumbnail mt-2" width="150">
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Guardar cambios</button>

                </form>
            </div>
        </div>
    </div>
    <?php include('../comp/footer.php'); ?>
</body>
</html>
