<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sistemapdf";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si la ID está presente en la URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Asegurarse de que la ID sea un entero

    // Consulta para obtener el archivo
    $sql = "SELECT nomArchivo, archivo FROM archivos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($nomArchivo, $archivo);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nomArchivo . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        echo $archivo;
    } else {
        echo "El archivo no existe. ID recibida: " . $id;
    }

    $stmt->close();
} else {
    echo "No se recibió ninguna ID.";
}

$conn->close();
?>
