<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'sistemapdf');

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']);
    $sql = "SELECT id, nomArchivo FROM archivos WHERE nomArchivo LIKE '%$query%'";
    $result = $conn->query($sql);

    $archivos = [];
    while ($row = $result->fetch_assoc()) {
        $archivos[] = $row;
    }

    echo json_encode($archivos);
}

$conn->close();
?>