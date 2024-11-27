<?php
include('../views/database.php');
/** @var mysqli $conn */
// Validar que se recibe un nivel mediante POST
if (isset($_POST['id_nivel'])) {
    $id_nivel = intval($_POST['id_nivel']);
    $sql = "SELECT * FROM niveles WHERE id_nivel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_nivel);
    $stmt->execute();
    $result = $stmt->get_result();
    $nivel = $result->fetch_assoc();

    if ($nivel): ?>
        <h3><?php echo htmlspecialchars($nivel['nombre']); ?></h3>
        <p><?php echo htmlspecialchars($nivel['descripcion']); ?></p>
        <a href="data:application/pdf;base64,<?php echo base64_encode($nivel['archivo']); ?>" download="nivel_<?php echo $nivel['id_nivel']; ?>.pdf">Descargar PDF</a>
        <div class="mt-3">
            <video controls>
                <source src="data:video/mp4;base64,<?php echo base64_encode($nivel['video']); ?>" type="video/mp4">
                Tu navegador no soporta el video.
            </video>
        </div>
    <?php endif;
} else {
    echo "Error: No se recibió un ID de nivel.";
}
?>
