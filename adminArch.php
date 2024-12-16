<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit;
}

$mensaje = '';
try {
    // Conexión a la base de datos
    $conn = new mysqli('localhost', 'root', '', 'sistemapdf');
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    
    // Manejo de carga y eliminación de archivos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $mensajes = [];
        if (isset($_FILES['archivo'])) {
            $archivos = $_FILES['archivo'];
            $maxFileSize = 64 * 1024 * 1024;
        
            if (count($archivos['name']) > 5) {
                $mensajes[] = 'No puedes subir más de 10 archivos a la vez.';
            } else {
                for ($i = 0; $i < count($archivos['name']); $i++) {
                    if ($archivos['size'][$i] > $maxFileSize) {
                        $mensajes[] = 'El archivo ' . $archivos['name'][$i] . ' excede el tamaño máximo permitido de 64 MB.';
                        continue;
                    }
                    
                    $nombre = $archivos['name'][$i];
                    $contenido = file_get_contents($archivos['tmp_name'][$i]);
                    $fecha = date('Y-m-d H:i:s', filemtime($archivos['tmp_name'][$i]));
        
                    $stmt = $conn->prepare("INSERT INTO archivos (nomArchivo, archivo, fecha) VALUES (?, ?, ?)");
                    $stmt->bind_param('sbs', $nombre, $null, $fecha);
                    $stmt->send_long_data(1, $contenido);
                    $stmt->execute();
                    $stmt->close();
                    $mensajes[] = "Archivo $nombre cargado con éxito!";
                }
            }
        } elseif (isset($_POST['eliminar'])) {
            if (isset($_POST['ids'])) {
                $ids = $_POST['ids'];
                foreach ($ids as $id) {
                    $stmt = $conn->prepare("DELETE FROM archivos WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $stmt->close();
                    $mensajes[] = "Archivo eliminado con éxito!";
                } 
            } else {
                $mensajes[] = "No se ha seleccionado ningún archivo para eliminar.";
            }
        } elseif (isset($_POST['eliminar_individual'])) {
            if (!isset($_POST['id'])) {
                throw new Exception("No se ha especificado un ID de archivo a eliminar.");
            }
            $id = $_POST['id'];
            $stmt = $conn->prepare("DELETE FROM archivos WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $mensajes[] = "Archivo eliminado con éxito!";

        }
    }
    
    // Obtener todos los archivos
    $result = $conn->query("SELECT * FROM archivos");
    if (!$result) {
        throw new Exception("Error al obtener los archivos: " . $conn->error);
    }

    // Ordenar los archivos por fecha o nombre

    $row = [];
    while ($archivo = $result->fetch_assoc()) {
        $row[] = $archivo;
    }


} catch (Exception $e) {
    echo "<script>alert('Error: " . $e->getMessage() . "')</script>";
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/adminarch.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <?php if ($_SESSION['rol'] === 1): ?>
        <title>Gestionar Archivos</title>
    <?php else: ?>
        <title>Ver Archivos</title>
    <?php endif; ?>
</head>
<body>
    <?php if (isset($mensajes)): ?>
        <script>alert('<?= implode("\\n", $mensajes) ?>');</script>
    <?php endif; ?>
    <?php if ($_SESSION['rol'] === 1): ?>
    <header>
        <nav>
            <ul>
                <li><a href="adminUsuarios.php" id="admin-link">Usuarios</a></li>
                <li><a href="adminArch.php">Archivos</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>
    <?php endif; ?>
    <div class="subtitulo">
        <h1>Documentos
            <span>Documentos disponibles sobre residencias.</span>
        </h1>
    </div>
    <?php if ($_SESSION['rol'] === 1): ?>
        <div class="subtitulo2" >
            <button class="ver" id="botonexpandir">Añadir archivo</button>
            <div id="camposExpandibles" class="hidden">
                <form class="nuevoar" method="POST" enctype="multipart/form-data">
                    <label for="archivo" class="ver">
                        Elegir archivos
                    </label>
                    <input type="file" id="archivo" name="archivo[]" accept=".pdf" multiple required style="display: none;">
                    <button id="subir" class="subir" type="submit">Subir</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <input type="text" id="buscarDocs" placeholder="Buscar archivos..." class="buscar">
    <form method="POST">
        <table border="1">
            <thead>
                <tr>
                    <th></th>
                    <th>Acciones</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
            <?php foreach ($row as $archivo): ?>
                    <tr id="row-<?= $archivo['id'] ?>">
                        <td class="check"><input type="checkbox" name="ids[]" value="<?= $archivo['id'] ?>"></td>
                        <td class="botonesfile">
                            <a class="verbtn" href="verArchivo.php?id=<?= $archivo['id']; ?>"><i class="material-icons">import_contacts</i></a>
                            <?php if ($_SESSION['rol'] === 1): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $archivo['id'] ?>">
                                    <button class="verelbtn" type="submit" name="eliminar_individual"><i class="material-icons">delete_sweep</i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td><?= $archivo['nomArchivo'] ?></td>
                        <td><?= $archivo['fecha'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="findocs">Fin de los documentos.</td>
                </tr>
            </tbody>
        </table>
        <?php if ($_SESSION['rol'] === 1): ?>
            <button class="ver eliminar-todos" type="submit" name="eliminar">Eliminar seleccionados</button>
        <?php endif; ?>
    </form>
    <button class="logout"><a href="logout.php">Cerrar sesión</a></button>
</body>
<footer>
    <p>&copy; 2024 Proyecto. Todos los derechos reservados.</p>
</footer>
<script>
    const userRole = <?= json_encode($_SESSION['rol']) ?>;
</script>
<script src="assets/js/adminArch.js"></script>
</html>

TODO Corregir ordenamiento de archivos por fecha y nombre
TODO Insertar logs de actividad en la base de datos
TODO Crear página de visualización de logs