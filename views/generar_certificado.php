<?php
require('../views/fpdf.php'); // Incluye la librería FPDF
session_start();
require 'database.php'; // Conexión a la base de datos
/** @var mysqli $conn */

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);

    // Consulta para obtener los datos del certificado
    $sql = "SELECT v.user_id, u.username, v.nombre_producto, v.fecha, v.idVendedor
            FROM usuarios u
            JOIN vista_ventas_completa v ON u.idUser = v.user_id
            WHERE v.estado = 'Finalizado' AND v.user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $nombreCliente = $row['username'];
        $nombreProducto = $row['nombre_producto'];
        $fechaFinalizacion = date('d/m/Y', strtotime($row['fecha']));
        $idVendedor = $row['idVendedor']; // Obtenemos el ID del vendedor

        // Ahora hacemos una consulta para obtener el nombre del vendedor con su ID
        $sqlVendedor = "SELECT username FROM usuarios WHERE idUser = ?";
        $stmtVendedor = mysqli_prepare($conn, $sqlVendedor);
        mysqli_stmt_bind_param($stmtVendedor, 'i', $idVendedor);
        mysqli_stmt_execute($stmtVendedor);
        $resultVendedor = mysqli_stmt_get_result($stmtVendedor);

        if ($resultVendedor && $rowVendedor = mysqli_fetch_assoc($resultVendedor)) {
            $nombreVendedor = $rowVendedor['username']; // Nombre del vendedor
        } else {
            $nombreVendedor = "Desconocido"; // Si no se encuentra el vendedor
        }

        // Crear el PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Agregar el logo
        $pdf->Image('../assets/logo.png', 10, 10, 30); // Cambia la ruta a tu logo

        // Título
        $pdf->Cell(0, 10, 'CERTIFICADO DE FINALIZACION', 0, 1, 'C');
        $pdf->Ln(10);

        // Contenido
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, utf8_decode("
            Este certificado reconoce que
            $nombreCliente

            Ha completado con éxito el curso de $nombreProducto.

            Por su dedicación, creatividad y compromiso, ha demostrado un alto nivel de habilidad en el uso de herramientas y técnicas avanzadas, alcanzando todos los objetivos del curso.
        "));

        $pdf->Ln(10);
        $pdf->Cell(0, 10, "Fecha: $fechaFinalizacion", 0, 1);
        $pdf->Cell(0, 10, "Instructor: $nombreVendedor", 0, 1);

        // Descargar el archivo
        $pdf->Output("D", "Certificado_$nombreCliente.pdf");
        exit;
    } else {
        echo "Datos no encontrados.";
    }
} else {
    echo "Acceso no autorizado.";
}
?>
