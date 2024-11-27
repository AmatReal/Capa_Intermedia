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

        
        <form class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="dateRange" class="form-label">Rango de Fechas</label>
                    <input type="date" class="form-control" id="startDate" name="startDate">
                    <input type="date" class="form-control mt-2" id="endDate" name="endDate">
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Categoría</label>
                    <select class="form-select" id="category" name="category">
                        <option value="all">Todas</option>
                        <option value="3d">3D</option>
                        <option value="animation">Animación</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Estado del Curso</label>
                    <select class="form-select" id="status" name="status">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
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
                
                <tr>
                    <td>Modelado 3D Básico</td>
                    <td>150</td>
                    <td>2.5</td>
                    <td>$15,000.00</td>
                </tr>
                <tr>
                    <td>Texturizado Avanzado en 3D</td>
                    <td>80</td>
                    <td>4.2</td>
                    <td>$12,500.00</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total por Forma de Pago</th>
                    <td>Tarjeta: $20,000.00, Paypal: $7,500.00</td>
                </tr>
            </tfoot>
        </table>

        
        <h2 class="mt-5 mb-3">Detalle de Ventas por Curso</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Fecha de Inscripción</th>
                    <th>Nivel de Avance</th>
                    <th>Precio Pagado</th>
                    <th>Forma de Pago</th>
                </tr>
            </thead>
            <tbody>
                
                <tr>
                    <td>Juan Pérez</td>
                    <td>15 Sep 2024</td>
                    <td>3.0</td>
                    <td>$100.00</td>
                    <td>Tarjeta</td>
                </tr>
                <tr>
                    <td>María García</td>
                    <td>02 Ago 2024</td>
                    <td>2.0</td>
                    <td>$150.00</td>
                    <td>Paypal</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4">Total del Curso</th>
                    <td>$250.00</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
