<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit;
}

include("basedatos.php");
    
function registrar_log($conn, $usuario, $accion, $descripcion) {
    $stmt = $conn->prepare("INSERT INTO logs (usuario, accion, descripcion) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $usuario, $accion, $descripcion);
    $stmt->execute();
    $stmt->close();
}

registrar_log($conn, $_SESSION['usuario'], 'Salida de sesión', 'Sesión cerrada.');
session_destroy();
header('Location: login.php');
exit();
?>
