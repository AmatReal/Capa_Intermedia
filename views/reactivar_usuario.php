<?php
// Conexión a la base de datos
require 'database.php'; // Asegúrate de incluir la conexión a la base de datos
/** @var mysqli $conn */
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUser = intval($_POST['idUser']); // Sanitiza el valor recibido

    // Validar que se haya enviado un ID
    if ($idUser > 0) {
        // Actualizar el status a 1 (activacion lógica)
        $sql = "UPDATE usuarios SET status = 1 WHERE idUser = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idUser);

        if ($stmt->execute()) {
          $result = $conn->query("SELECT idUser, email, username, full_name, birthdate, gender, created_at FROM usuarios WHERE idUser = $idUser");
          $usuario = $result->fetch_assoc();


          
          $filaHtml = "<tr id='fila-{$usuario['idUser']}'>
                   <td>{$usuario['idUser']}</td>
                   <td>{$usuario['email']}</td>
                   <td>{$usuario['username']}</td>
                   <td>{$usuario['full_name']}</td>
                   <td>" . date('d M Y', strtotime($usuario['birthdate'])) . "</td>
                   <td>{$usuario['gender']}</td>
                   <td>" . date('d M Y', strtotime($usuario['created_at'])) . "</td>
                   <td>
                     <button class='btn btn-danger btn-sm' onclick='eliminarUsuario({$usuario['idUser']})'>Eliminar</button>
                   </td>
                 </tr>";

            echo json_encode(['success' => true, 'message' => 'Usuario reactivado correctamente.', 'filaHtml' => $filaHtml]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al reactivado el usuario.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
