<?php
$servername = "db-dtx-03.sparkedhost.us:3306";
$username = "u136076_3Ngb7EK4AQ";
$password = "hXmgELjE1okLQE=5zUErt@V.";
$dbname = "s136076_sistemapdf";

// Crear la conexión y configurar el timeout
$conn = new mysqli();
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 180);
$conn->real_connect($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

function registrar_log($conn, $usuario, $accion, $descripcion) {
    $stmt = $conn->prepare("INSERT INTO logs (usuario, accion, descripcion) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $usuario, $accion, $descripcion);
    $stmt->execute();
    $stmt->close();
}
?>
