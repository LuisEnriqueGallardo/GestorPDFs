<?php
session_start();

$mensaje = '';

try {
    // Conexión a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'sistemapdf');
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Manejo de creación y eliminación de usuarios
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['usuario']) && isset($_POST['contrasenia']) && isset($_POST['rol'])) {
            $usuario = $_POST['usuario'];
            $contrasenia = $_POST['contrasenia'];
            $rol = $_POST['rol'];

            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, contrasenia, es_admin) VALUES (?, ?, ?)");
            $stmt->bind_param('ssi', $usuario, $contrasenia, $rol);
            $stmt->execute();
            $mensaje = "Usuario creado con éxito!";
            $stmt->close();
            
        } elseif (isset($_POST['eliminar'])) {
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conn->error);
            }
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
            $mensaje = "Usuario eliminado con éxito!";
            $stmt->close();
        }
    }
    // Obtener todos los usuarios
    $result = $conn->query("SELECT * FROM usuarios");
    if (!$result) {
        throw new Exception("Error al obtener los usuarios: " . $conn->error);
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
    echo "<script>console.error('HOLA ERROR 1: " . $error_message . "');</script>";
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/adminUsuarios.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Gestionar Usuarios</title>
</head>
<body>
    <div class="subtitulo">
        <h1>Usuarios
            <span>Gestióna los usuarios desde este apartado.</span>
        </h1>
    </div>
    <?php if ($mensaje): ?>
        <script>alert('<?= $mensaje ?>');</script>
    <?php endif; ?>
    <div class="subtitulo">
            <h1>
                <span>Usuarios existentes</span>
            </h1>
        <button class="logout"><a href="adminArch.php">Volver</a></button>
        <a href="logout.php">Cerrar Sesión</a>
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