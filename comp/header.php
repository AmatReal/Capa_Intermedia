<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['moneda'])) {
    $_SESSION['moneda'] = 'MXN';
}

$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Obtener la hora actual usando la API de AbstractAPI
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://timezone.abstractapi.com/v1/current_time?api_key=e352ac3f7ceb454d94d379d7925f88dd&location=Mexico",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

$horaActual = "No disponible";

if (!$err) {
    $data = json_decode($response, true);
    if (isset($data['datetime'])) {
        $horaActual = date("h:i A", strtotime($data['datetime']));
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .dropdown-search {
            position: absolute;
            background-color: #fff;
            z-index: 1000;
            width: 100%;
            border: 1px solid #ccc;
        }
        .dropdown-search-item {
            padding: 8px 12px;
            cursor: pointer;
        }
        .dropdown-search-item:hover {
            background-color: #f0f0f0;
        }
        .current-time {
            color: white;
            font-size: 16px;
            margin-right: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Peaceframe Market</a>
        <div class="current-time">
            Hora actual: <?php echo $horaActual; ?>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <!-- Barra de búsqueda -->
                <form class="d-flex position-relative" style="width: 300px;">
                    <input class="form-control me-2" type="search" placeholder="Buscar cursos" aria-label="Buscar" id="searchInput">
                    <div id="searchResults" class="dropdown-search" style="display: none;"></div>
                </form>
                <li class="nav-item">
                    <a class="nav-link" href="../views/menu.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/explorar.php">Cursos</a>
                </li>
                <?php if ($_SESSION['role'] == 'administrador'): ?>
                   <li class="nav-item">
                       <a class="nav-link" href="../views/solicitudes.php">Solicitudes</a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link" href="../views/reporte_usuarios.php">Reporte Usuarios</a>
                   </li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] == 'vendedor'): ?>
                   <li class="nav-item">
                       <a class="nav-link" href="../views/crearproducto.php">Crear Curso</a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link" href="../views/consulta_ventas.php">Ventas</a>
                   </li>
                <?php endif; ?>
                <?php if ($_SESSION['role'] == 'cliente'): ?>
                   <li class="nav-item">
                       <a class="nav-link" href="../views/carrito.php">Carrito</a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link" href="../views/mis_pedidos.php">Pedidos</a>
                   </li>
                <?php endif; ?>
                <!-- Enlace dinámico para los chats -->
                <li class="nav-item">
                    <a class="nav-link" href="../views/mis_chats.php">Chat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/perfil.php">Perfil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../views/logout.php">Salir</a>
                </li>
            </ul>
        </div>
    </div>
    <!-- Cambiar moneda -->
    <!-- <form action="../comp/cambiar_moneda.php" method="post" style="display: inline;">
        <button type="submit" name="moneda" value="<?php echo $_SESSION['moneda'] === 'MXN' ? 'USD' : 'MXN'; ?>" class="btn btn-light btn-sm">
            Cambiar a <?php echo $_SESSION['moneda'] === 'MXN' ? 'Dólares (USD)' : 'Pesos (MXN)'; ?>
        </button>
    </form> -->
</nav>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>


<!-- <script>
    $(document).ready(function () {
        // Evento al escribir en el campo de búsqueda
        $('#searchInput').on('input', function () {
            let query = $(this).val();

            if (query.length > 1) { // Iniciar búsqueda cuando haya más de 1 caracter
                $.ajax({
                    url: '../views/buscarProducto.php', // Archivo PHP que realiza la búsqueda
                    method: 'POST',
                    data: { search: query },
                    success: function (data) {
                        $('#searchResults').html(data).show();
                    }
                });
            } else {
                $('#searchResults').hide();
            }
        });

        // Evento para seleccionar un producto
        $(document).on('click', '.dropdown-search-item', function () {
            let productId = $(this).data('id');
            window.location.href = `producto.php?id=${productId}`; // Redirigir a la página del producto
        });

        // Ocultar resultados al hacer clic fuera
        $(document).click(function (e) {
            if (!$(e.target).closest('#searchInput').length) {
                $('#searchResults').hide();
            }
        });
    });
</script> -->

</body>
</html>
