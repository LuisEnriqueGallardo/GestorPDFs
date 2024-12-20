<?php
session_start();
include("basedatos.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contrasenia'];

    $stmt = $conn->prepare("SELECT id, usuario, es_admin FROM usuarios WHERE usuario = ? AND contrasenia = ?");
    $stmt->bind_param('ss', $usuario, $contraseña);
    $stmt->execute();
    $result = $stmt->get_result();

    
    if ($result->num_rows === 1) {
        // Registrar log de inicio de sesión
        registrar_log($conn, $usuario, 'Inicio de sesión', 'Inicio de sesión exitoso.');

        $user = $result->fetch_assoc();
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['es_admin'];
        header('Location: adminArch.php');
    } else {
        // Registrar log de inicio de sesión fallido
        registrar_log($conn, $usuario, 'Inicio de sesión', 'Inicio de sesión fallido.');
        echo "<script>alert('Usuario o contraseña incorrectos.');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>