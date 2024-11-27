<?php
require "database.php";
/** @var mysqli $conn */
if (isset($_POST['submit'])) {
    // Validaciones del lado del servidor
    $errors = [];
    $successMessage = "";

    // Validar correo electrónico
    if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Por favor, ingresa un correo electrónico válido.";
    }

    // Validar nombre de usuario
    if (empty($_POST['username']) || strlen($_POST['username']) < 3) {
        $errors[] = "El nombre de usuario debe tener al menos 3 caracteres.";
    }

    // Validar contraseña
    $password = $_POST['password'];
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "La contraseña debe incluir al menos una letra mayúscula.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "La contraseña debe incluir al menos una letra minúscula.";
    } elseif (!preg_match('/[\W]/', $password)) {
        $errors[] = "La contraseña debe incluir al menos un carácter especial.";
    }

    // Verificar que las contraseñas coincidan
    if ($password !== $_POST['confirmPassword']) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    // Validar nombre completo
    $fullName = $_POST['fullName'];
    if (empty($fullName) || strlen($fullName) < 3) {
        $errors[] = "El nombre completo debe tener al menos 3 caracteres.";
    } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $fullName)) {
        $errors[] = "El nombre completo solo puede contener letras y espacios.";
    }

    // Validar fecha de nacimiento
    $birthdate = $_POST['birthdate'];
    if (empty($birthdate) || !DateTime::createFromFormat('Y-m-d', $birthdate)) {
        $errors[] = "Por favor, ingresa una fecha de nacimiento válida.";
    } elseif (strtotime($birthdate) > time()) {
        $errors[] = "La fecha de nacimiento no puede ser mayor a la fecha actual.";
    }

    // Validar rol
    if (!in_array($_POST['role'], ['alumno', 'instructor', 'administrador'])) {
        $errors[] = "El rol seleccionado no es válido.";
    }

    // Validar avatar (opcional)
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
            $errors[] = "El avatar debe ser un archivo de imagen (JPG, PNG o GIF).";
        }
    }

    // Si no hay errores, procesar el registro
    if (empty($errors)) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $username = htmlspecialchars($_POST['username']);
        $role = $_POST['role'];
        $gender = $_POST['gender'];

        // Manejo del avatar
        $avatarData = null;
        $avatarType = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $avatarFile = $_FILES['avatar'];
            $avatarType = $avatarFile['type'];
            $avatarData = file_get_contents($avatarFile['tmp_name']);
        }

        // Insertar datos en la base de datos
        $query = "INSERT INTO usuarios (full_name, username, email, password, gender, birthdate, role) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $fullName, $username, $email, $password, $gender, $birthdate, $role);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $stmt->close();

            // Si se subió un avatar, insertarlo
            if ($avatarData !== null) {
                $insertAvatarQuery = "INSERT INTO avatares (tipo, archivo, idUserM) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insertAvatarQuery);
                $stmt->bind_param("sbi", $avatarType, $avatarData, $userId);
                $stmt->send_long_data(1, $avatarData);
                $stmt->execute();
                $avatarId = $stmt->insert_id;
                $stmt->close();

                // Asociar avatar al usuario
                $updateUserQuery = "UPDATE usuarios SET avatar = ? WHERE idUser = ?";
                $stmt = $conn->prepare($updateUserQuery);
                $stmt->bind_param("ii", $avatarId, $userId);
                $stmt->execute();
                $stmt->close();
            }

            $successMessage = "Registro exitoso. Redirigiendo al login...";
            echo "<script>setTimeout(() => window.location.href = 'login.php', 3000);</script>";
        } else {
            $errors[] = "Error al registrar el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Validar nombre completo para que solo permita letras y espacios
            document.getElementById('fullName').addEventListener('input', function (e) {
                const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/;
                if (!regex.test(e.target.value)) {
                    e.target.value = e.target.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
                }
            });
        });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Peaceframe Market</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Registro de Usuario</h2>

                <!-- Mostrar errores como pop-ups -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Mostrar mensaje de éxito como pop-up -->
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $successMessage; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="post" id="registrationForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Nombre de Usuario:</label>
                        <input type="text" class="form-control" id="username" name="username" minlength="3" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirmar Contraseña:</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Rol de Usuario:</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="alumno">Alumno</option>
                            <option value="instructor">Instructor</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="avatar">Imagen de Avatar:</label>
                        <input type="file" class="form-control-file" id="avatar" name="avatar" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="fullName">Nombre Completo:</label>
                        <input type="text" class="form-control" id="fullName" name="fullName" required>
                    </div>

                    <div class="form-group">
                        <label for="birthdate">Fecha de Nacimiento:</label>
                        <input type="date" class="form-control" id="birthdate" name="birthdate" required>
                    </div>

                    <div class="form-group">
                        <label for="gender">Género:</label>
                        <select class="form-control" id="gender" name="gender" required>
                            <option value="masculino">Masculino</option>
                            <option value="femenino">Femenino</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <button type="submit" value="Enviar" name="submit" class="btn btn-primary btn-block">Registrar</button>

                    <div class="text-center mt-3">
                        <a href="../views/login.php" class="btn btn-link">Regresar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
