<?php
session_start(); // Iniciar la sesión

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si el usuario no está logueado, redirige o muestra un mensaje de error
    echo "Por favor, inicie sesión para ver este reporte.";
    exit();
}

require 'database.php'; // Conexión a la base de datos

// Definir los parámetros de búsqueda (fecha, categoría, estado del curso)
$startDate = isset($_POST['startDate']) ? $_POST['startDate'] : null;
$endDate = isset($_POST['endDate']) ? $_POST['endDate'] : null;
$category = isset($_POST['category']) ? $_POST['category'] : 'all';
$status = isset($_POST['status']) ? $_POST['status'] : 'all';

// Obtener las categorías desde la base de datos
$queryCategorias = "SELECT * FROM categorias";
$resultCategorias = mysqli_query($conn, $queryCategorias);
$categorias = mysqli_fetch_all($resultCategorias, MYSQLI_ASSOC);

// Obtener el ID del usuario activo (esto depende de tu implementación de sesión)
$userId = $_SESSION['user_id']; // El ID del usuario logueado

// Inicializar la consulta básica
$query = "SELECT p.nombre_producto, 
                 SUM(v.total) AS total_ingresos, 
                 COUNT(DISTINCT v.user_id) AS alumnos_inscritos
          FROM ventas v
          JOIN productos p ON v.idProducto = p.idProducto
          WHERE 1";

// Agregar filtros
if ($startDate && $endDate) {
    $query .= " AND v.fecha BETWEEN '$startDate' AND '$endDate'";
}
if ($category && $category != 'all') {
    $query .= " AND p.id_categoria = '$category'";
}
if ($status && $status != 'all') {
    $query .= " AND v.estado = '$status'";
}

// Agrupar por curso
$query .= " GROUP BY p.idProducto";

// Ejecutar la consulta
$result = mysqli_query($conn, $query);

// Verificar si se obtuvieron resultados
if ($result && mysqli_num_rows($result) > 0) {
    $cursos = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $cursos = [];
}

// Consulta para el total de ventas del usuario activo y su total de ventas por producto
$queryTotalVentasUsuario = "SELECT IFNULL(SUM(v.total), 0) AS total_ventas
                            FROM ventas v
                            JOIN productos p ON v.idProducto = p.idProducto
                            WHERE v.user_id = '$userId' AND v.estado = 'Finalizado'";
$resultTotalVentasUsuario = mysqli_query($conn, $queryTotalVentasUsuario);
$totalVentasUsuario = mysqli_fetch_assoc($resultTotalVentasUsuario)['total_ventas'];

// Generar la tabla con los resultados
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
    <link href="../bootstrap_css/bootstrap.min.css" rel="stylesheet">
    <?php include('../comp/header.php'); ?>
    <link href="../css/ventas.css" rel="stylesheet">
    <style>
        .table thead th {
            background-color: #6f42c1; 
            color: white;
        }
        .table tbody td {
            color: black;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Reporte de Ventas de Cursos</h1>
        
        <form class="mb-4" method="POST">
            <div class="row">
                <div class="col-md-3">
                    <label for="dateRange" class="form-label">Rango de Fechas</label>
                    <input type="date" class="form-control" id="startDate" name="startDate" value="<?php echo $startDate; ?>">
                    <input type="date" class="form-control mt-2" id="endDate" name="endDate" value="<?php echo $endDate; ?>">
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Categoría</label>
                    <select class="form-select" id="category" name="category">
                        <option value="all" <?php echo ($category == 'all') ? 'selected' : ''; ?>>Todas</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['idCat']; ?>" <?php echo ($category == $categoria['idCat']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Estado del Curso</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all" <?php echo ($status == 'all') ? 'selected' : ''; ?>>Todos</option>
                        <option value="Activo" <?php echo ($status == 'Activo') ? 'selected' : ''; ?>>Activos</option>
                        <option value="Finalizado" <?php echo ($status == 'Finalizado') ? 'selected' : ''; ?>>Finalizados</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
                </div>
            </div>
        </form>

        <h2 class="mb-3">Ventas por Curso</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Curso</th>
                    <th>Alumnos Inscritos</th>
                    <th>Nivel Promedio</th>
                    <th>Total de Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cursos as $curso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($curso['nombre_producto']); ?></td>
                        <td><?php echo htmlspecialchars($curso['alumnos_inscritos']); ?></td>
                        <td>Sin Datos</td> <!-- Aquí puedes agregar el cálculo si tienes datos sobre el nivel promedio -->
                        <td><?php echo "$" . number_format($curso['total_ingresos'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total Vendido por el Usuario</th>
                    <td><?php echo "$" . number_format($totalVentasUsuario, 2); ?></td>
                </tr>
            </tfoot>
        </table>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
