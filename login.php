<?php
session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contrasenia'];

    // Conexión a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'sistemapdf');
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    $sql = "SELECT id, usuario, es_admin FROM usuarios WHERE usuario = ? AND contrasenia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $usuario, $contraseña);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        session_start();
        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['rol'] = $user['es_admin'];
        header('Location: adminArch.php');
    } else {
        echo 'Credenciales incorrectas';
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="caja-login">
        <h2>Iniciar Sesión</h2>
        <form method="POST" action="login.php">
            <div class="recuadro">
                <input type="text" name="usuario" required>
                <label>Usuario</label>
            </div>
            <div class="recuadro">
                <input type="password" name="contrasenia" required>
                <label>Contraseña</label>
            </div>
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>