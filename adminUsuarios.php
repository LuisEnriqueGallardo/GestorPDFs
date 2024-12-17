<?php
session_start();
include("basedatos.php");

if (!isset($_SESSION['rol'])) {
    header('Location: index.html');
    exit;
}

$mensaje = '';

try {

    // Manejo de creación y eliminación de usuarios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['usuario']) && isset($_POST['contrasenia']) && isset($_POST['rol'])) {
            $usuario = $_POST['usuario'];
            $contrasenia = $_POST['contrasenia'];
            $rol = $_POST['rol'];

            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasenia, es_admin) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $usuario, $contrasenia, $rol);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('Usuario creado con éxito!');</script>";

            // Registrar log de creación de usuario
            registrar_log($conn, $_SESSION['usuario'], 'Creación de usuario', "Usuario '$usuario' creado.");

            // Redirigir para evitar reenvío de formulario
            header('Location: adminUsuarios.php');
            exit;
            
        } elseif (isset($_POST['eliminar'])) {
            $id = $_POST['id'];

            $usuarioNom = $conn->query("SELECT usuario FROM usuarios WHERE id = $id");
            $resultado = $usuarioNom->fetch_assoc();
            $nombreUsuario = $resultado['usuario'];

            if ($nombreUsuario === $_SESSION['usuario']) {
                echo "<script>alert('No puedes eliminar tu propio usuario.'); window.location.href='adminUsuarios.php';</script>";
                exit;
            }
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            echo "<script>alert('Usuario eliminado con éxito!');</script>";

            // Registrar log de eliminación de usuario
            registrar_log($conn, $_SESSION['usuario'], 'Eliminación de usuario', "Usuario '$nombreUsuario' eliminado.");

            // Redirigir para evitar reenvío de formulario
            header('Location: adminUsuarios.php');
            exit;
        }
    }
    // Obtener todos los usuarios
    $result = $conn->query("SELECT * FROM usuarios");
    if (!$result) {
        throw new Exception("Error al obtener los usuarios: " . $conn->error);
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
    echo "<script>alert('ERROR: " . $error_message . "');</script>";
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/adminUsuarios.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=logout" rel="stylesheet" />
    <title>Gestionar Usuarios</title>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="adminArch.php">Documentos</a></li>
                <li><a href="adminUsuarios.php">Usuarios</a></li>
                <li><a href="logs.php">Registros</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>
    <div class="subtitulo">
        <h1>Usuarios
            <span>Gestióna los usuarios desde este apartado.</span>
        </h1>
    </div>
    <div class="separador">
        <div class="contenedorreg">
            <form class="form" method="POST" action="">
                <div class="registro">
                    <div class="detalles">Crear usuario</div>
                    <input name="usuario" placeholder="Usuario" class="entrada" type="text" required>
                    <input name="contrasenia" placeholder="Contrasenia" class="entrada" type="password" required>
                    <select name="rol" class="entrada" required>
                        <option value="0">Usuario</option>
                        <option value="1">Administrador</option>
                    </select>
                    <button class="btn" type="submit">Crear</button>
                </div>
            </form>
        </div>
        <table border="1">
            <tr>
                <th>No.</th>
                <th>Nombre</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
            <?php if ($result): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['usuario'] ?></td>
                        <td>
                            <?php
                            if ($row['es_admin'] == 1) {
                                echo 'Administrador';
                            } else {
                                echo 'Usuario';
                            }
                            ?>
                        </td>
                        <td>
                            <form method="POST" action="adminUsuarios.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button class="verelbtn" type="submit" name="eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');"><i class="material-icons">delete_sweep</i></button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>
    </div>
</body>
<footer>
    <p>&copy; 2024 Proyecto. Todos los derechos reservados.</p>
</footer>
</html>
