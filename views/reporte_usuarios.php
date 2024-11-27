<?php
require 'database.php'; // Conexión a la base de datos
/** @var mysqli $conn */
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Obtener datos del formulario
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Preparar consulta SQL
  $stmt = mysqli_prepare($conn, "SELECT idUser, username, email, avatar, role FROM usuarios WHERE email = ? AND password = ?");
  mysqli_stmt_bind_param($stmt, "ss", $email, $password); // 'ss' indica que ambos parámetros son strings

  // Ejecutar la consulta
  mysqli_stmt_execute($stmt);

  // Obtener resultados
  $result = mysqli_stmt_get_result($stmt);

  // Verificar si las credenciales son correctas
  if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);

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
  } else {
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
  <title>Reporte de Usuarios</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="reporte.css">
  <?php include('../comp/header.php'); ?>
</head>

<body>
  <div class="container mt-5">
    <h1>Reporte de Usuarios</h1>

    <form method="GET" action="reporte_usuarios.php">
      <div class="form-group">
        <label for="tipo_usuario">Selecciona el tipo de usuario</label>
        <select id="tipo_usuario" name="tipo_usuario" class="form-control" onchange="this.form.submit()">
          <option value="">Selecciona</option>
          <option value="vendedor" <?php if (isset($_GET['tipo_usuario']) && $_GET['tipo_usuario'] === 'vendedor') echo 'selected'; ?>>Instructores</option>
          <option value="cliente" <?php if (isset($_GET['tipo_usuario']) && $_GET['tipo_usuario'] === 'cliente') echo 'selected'; ?>>Estudiantes</option>
        </select>
      </div>
    </form>

    <?php
    $tipo_usuario = isset($_GET['tipo_usuario']) ? $_GET['tipo_usuario'] : '';

    if ($tipo_usuario === 'vendedor') {
      $sql = "SELECT idUser, email, username, avatar, full_name, birthdate, gender, created_at 
          FROM usuarios 
          WHERE role = 'vendedor'
          AND status = 1"; //trae todos los usuario vndedores
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        $instructores = $result->fetch_all(MYSQLI_ASSOC);
      } else {
        $instructores = []; // No hay datos
      }
    }

    if ($tipo_usuario === 'cliente') {
      $sql = "SELECT idUser, email, username, avatar, full_name, birthdate, gender, created_at 
              FROM usuarios 
              WHERE role = 'cliente' 
              AND status = 1"; //trae todos los clientes activos
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        $estudiantes = $result->fetch_all(MYSQLI_ASSOC);
      } else {
        $estudiantes = []; // No hay datos
      }
    }
    ?>

    <?php if ($tipo_usuario === 'vendedor'): ?>
      <h2>Instructores</h2>
      <table id="tabla-activos" class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Username</th>
            <th>Nombre Completo</th>
            <th>Fecha de Nacimiento</th>
            <th>Género</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($instructores as $row): ?>
            <tr id="fila-<?php echo $row['idUser']; ?>">
              <td><?php echo htmlspecialchars($row['idUser']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['birthdate']))); ?></td>
              <td><?php echo htmlspecialchars($row['gender']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
              <td>
                <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(<?php echo $row['idUser']; ?>)">Eliminar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <?php if ($tipo_usuario === 'cliente'): ?>

      <h2>Estudiantes</h2>
      <table id="tabla-activos" class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Username</th>
            <th>Nombre Completo</th>
            <th>Fecha de Nacimiento</th>
            <th>Género</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($estudiantes as $row): ?>
            <tr id="fila-<?php echo $row['idUser']; ?>">
              <td><?php echo htmlspecialchars($row['idUser']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['birthdate']))); ?></td>
              <td><?php echo htmlspecialchars($row['gender']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
              <td>
                <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(<?php echo $row['idUser']; ?>)">Eliminar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>


    <?php
    $tipo_usuario = isset($_GET['tipo_usuario']) ? $_GET['tipo_usuario'] : '';

    if ($tipo_usuario === 'vendedor') {
      $sql = "SELECT idUser, email, username, avatar, full_name, birthdate, gender, created_at 
          FROM usuarios 
          WHERE role = 'vendedor'
          AND status = 0"; //trae todos los usuario vndedores
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        $instructores = $result->fetch_all(MYSQLI_ASSOC);
      } else {
        $instructores = []; // No hay datos
      }
    }

    if ($tipo_usuario === 'cliente') {
      $sql = "SELECT idUser, email, username, avatar, full_name, birthdate, gender, created_at 
              FROM usuarios 
              WHERE role = 'cliente' 
              AND status = 0"; //trae todos los clientes activos
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        $estudiantes = $result->fetch_all(MYSQLI_ASSOC);
      } else {
        $estudiantes = []; // No hay datos
      }
    }
    ?>



    <?php if ($tipo_usuario === 'vendedor'): ?>
      <h2>Instructores Inactivos</h2>
      <table id="tabla-inactivos" class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Username</th>
            <th>Nombre Completo</th>
            <th>Fecha de Nacimiento</th>
            <th>Género</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($instructores as $row): ?>
            <tr id="fila-<?php echo $row['idUser']; ?>">
              <td><?php echo htmlspecialchars($row['idUser']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['birthdate']))); ?></td>
              <td><?php echo htmlspecialchars($row['gender']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
              <td>
                <button class="btn btn-danger btn-sm" onclick="activarUsuario(<?php echo $row['idUser']; ?>)">Re Activar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <?php if ($tipo_usuario === 'cliente'): ?>

      <h2>Estudiantes Inactivos</h2>
      <table id="tabla-inactivos" class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Username</th>
            <th>Nombre Completo</th>
            <th>Fecha de Nacimiento</th>
            <th>Género</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($estudiantes as $row): ?>
            <tr id="fila-<?php echo $row['idUser']; ?>">
              <td><?php echo htmlspecialchars($row['idUser']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo htmlspecialchars($row['full_name']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['birthdate']))); ?></td>
              <td><?php echo htmlspecialchars($row['gender']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
              <td>
                <button class="btn btn-secondary btn-sm" onclick="activarUsuario(<?php echo $row['idUser']; ?>)">Re Activar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <?php include('../comp/footer.php'); ?>

  <script>
    function eliminarUsuario(idUser) {
      if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        fetch('eliminar_usuario.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `idUser=${idUser}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert(data.message);
              // Mover la fila a la tabla de inactivos
              const fila = document.getElementById(`fila-${idUser}`);
              if (fila) {
                fila.remove(); // Eliminar de la tabla actual
                const tablaInactivos = document.querySelector("#tabla-inactivos tbody");
                if (tablaInactivos) {
                  tablaInactivos.innerHTML += data.filaHtml; // Agregar la fila actualizada
                }
              }
            } else {
              alert(data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al eliminar el usuario.');
          });
      }
    }

    function activarUsuario(idUser) {
      if (confirm('¿Estás seguro de que deseas reactivar este usuario?')) {
        fetch('reactivar_usuario.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `idUser=${idUser}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              alert(data.message);
              // Mover la fila a la tabla de activos
              const fila = document.getElementById(`fila-${idUser}`);
              if (fila) {
                fila.remove(); // Eliminar de la tabla actual
                const tablaActivos = document.querySelector("#tabla-activos tbody");
                if (tablaActivos) {
                  tablaActivos.innerHTML += data.filaHtml; // Agregar la fila actualizada
                }
              }
            } else {
              alert(data.message);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema al reactivar el usuario.');
          });
      }
    }
  </script>
</body>

</html>