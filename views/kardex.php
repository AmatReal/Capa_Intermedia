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
  <title>Kardex de Cursos</title>
  <link rel="stylesheet" href="../bootstrap_css/bootstrap.min.css">
  <?php include('../comp/header.php'); ?>
</head>

<body>
  <div class="container mt-4">


    <h1 class="mb-4">Kardex de Cursos</h1>
    <form id="filtros-form" class="mb-4">
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="fecha-inicio">Fecha de Inicio:</label>
          <input type="date" id="fecha-inicio" name="fecha-inicio" class="form-control">
        </div>
        <div class="col-md-4">
          <label for="fecha-fin">Fecha de Fin:</label>
          <input type="date" id="fecha-fin" name="fecha-fin" class="form-control">
        </div>

        <?php

          $sql = "SELECT * 
            FROM vista_categoria"; //categorias de los cursos
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            $categorias = $result->fetch_all(MYSQLI_ASSOC);
          } else {
            $categorias = []; // No hay datos
          }

        ?>

        <div class="col-md-4">
          <label for="categoria">Categoría:</label>
          <select id="categoria" name="categoria" class="form-control">
            <option value="">Todas</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>">
                <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>


      </div>
      <div class="row mb-3">
        <div class="col-md-6">
          <label for="estado">Estado:</label>
          <select id="estado" name="estado" class="form-control">
            <option value="">Todos</option>
            <option value="terminado">Solo Cursos Terminados</option>
            <option value="activo">Solo Cursos Activos</option>
          </select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
      </div>
    </form>



    <form method="GET" action="kardex.php">
      <div class="form-group">
        <label for="estado_curso">Selecciona el estado de curso</label>
        <select id="estado_curso" name="estado_curso" class="form-control" onchange="this.form.submit()">
          <option value="">Selecciona</option>
          <option value="Activo" <?php if (isset($_GET['estado_curso']) && $_GET['estado_curso'] === 'Activo') echo 'selected'; ?>>Activo</option>
          <option value="Finalizado" <?php if (isset($_GET['estado_curso']) && $_GET['estado_curso'] === 'Finalizado') echo 'selected'; ?>>Finalizado</option>
        </select>
      </div>
    </form>

    <?php
    $estado_curso = isset($_GET['estado_curso']) ? $_GET['estado_curso'] : '';

    if ($estado_curso === 'Activo') {
      $sql = "SELECT * 
          FROM vista_ventas_completa
          WHERE estado = 'Activo'
          AND user_id = '" . $_SESSION['user_id'] . "'"; //cursos del usuario 
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        $kardex = $result->fetch_all(MYSQLI_ASSOC);
      } else {
        $kardex = []; // No hay datos
      }
    }

    if ($estado_curso === 'Finalizado') {
      $sql = "SELECT *
              FROM vista_ventas_completa 
              WHERE estado = 'Finalizado' 
              AND user_id = '" . $_SESSION['user_id'] . "'"; //trae todos los clientes activos
      $result = $conn->query($sql);

      if ($result && $result->num_rows > 0) {
        $finalizados = $result->fetch_all(MYSQLI_ASSOC);
      } else {
        $finalizados = []; // No hay datos
      }
    }
    ?>



    <?php if ($estado_curso === 'Activo'): ?>
      <h2>Cursos Activos</h2>
      <table id="tabla-activos" class="table table-bordered">
        <thead>
          <tr>
            <th>Curso</th>
            <th>Categoria</th>
            <th>Fecha de Inscripcion</th>
            <th>Última Fecha de Ingreso</th>
            <th>Progreso</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($kardex as $row): ?>
              <tr id="fila-<?php echo $row['nombre_producto']; ?>">
              <td><?php echo htmlspecialchars($row['categoria']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['fecha']))); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['ultima_fecha_de_ingreso']))); ?></td>
              <td><?php //echo htmlspecialchars($row['']); 
                  ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>


    <?php if ($estado_curso === 'Finalizado'): ?>
      <h2>Cursos Finalizados</h2>
      <table id="tabla-activos" class="table table-bordered">
        <thead>
          <tr>
            <th>Curso</th>
            <th>Categoria</th>
            <th>Fecha de Inscripcion</th>
            <th>Última Fecha de Ingreso</th>
            <th>Progreso</th>
            <th>Estado</th>
            <th>Certificado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($finalizados as $row): ?>
            <tr id="fila-<?php echo $row['producto']; ?>">
              <td><?php echo htmlspecialchars($row['categoria']); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['fecha_inscripcion']))); ?></td>
              <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['ultima_fecha_ingreso']))); ?></td>
              <td><?php //echo htmlspecialchars($row['']); 
                  ?></td>
              <td>
                <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(<?php echo $row['idUser']; ?>)">Eliminar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>



  </div>
  <?php include('../comp/footer.php'); ?>
</body>

</html>