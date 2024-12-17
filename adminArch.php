<?php
session_start();
include("basedatos.php");

if (!isset($_SESSION['rol'])) {
    header('Location: index.html');
    exit;
}

$mensaje = '';
try {  
    // Manejo de carga y eliminación de archivos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = $_SESSION['usuario'];
        $mensajes = [];
        if (isset($_FILES['archivo'])) {
            $archivos = $_FILES['archivo'];
            $maxFileSize = 10 * 1024 * 1024;
        
            if (count($archivos['name']) > 5) {
                $mensajes[] = 'No puedes subir más de 5 archivos a la vez.';
            } else {
                for ($i = 0; $i < count($archivos['name']); $i++) {
                    
                    if ($archivos['size'][$i] > $maxFileSize) {
                        $mensajes[] = 'El archivo ' . $archivos['name'][$i] . ' excede el tamaño máximo permitido de 10MB.';
                        continue;
                    }
                    
                    $nombre = $archivos['name'][$i];
                    $contenido = file_get_contents($archivos['tmp_name'][$i]);
                    $fecha = date('Y-m-d H:i:s', filemtime($archivos['tmp_name'][$i]));

                    // Se convierte la fecha a la zona horaria local para no usar la del servidor
                    $fechaLocal = new DateTime('now', new DateTimeZone('America/Mexico_City'));
                    $fechaLocal = $fechaLocal->format('Y-m-d H:i:s');

                    $tamano = $archivos['size'][$i];  // Tamaño del archivo
                    $tamano = round($tamano / 1024, 2); // Convertir a KB

                    $stmt = $conn->prepare("INSERT INTO archivos (nomArchivo, archivo, fecha, tamano) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('sbsi', $nombre, $null, $fechaLocal, $tamano);  // 's' para string, 'b' para blob, 'i' para entero
                    $stmt->send_long_data(1, $contenido);  // El archivo se enviará como blob
                    $stmt->execute();
                    $stmt->close();

                    
                    echo "<script>alert('Archivo subido con éxito.');</script>";
                    // Registrar log de subida de archivo
                    registrar_log($conn, $usuario, 'Subida de archivo', "Archivo '$nombre' subido.");

                }
                // Mostrar todos los mensajes de alerta juntos
                if (!empty($mensajes)) {
                    echo "<script>alert('" . implode("\\n", $mensajes) . "');</script>";
                }
            // Redirigir para evitar reenvío de formulario
            header('Location: adminArch.php');
            exit;
            }
        } elseif (isset($_POST['eliminar'])) {
            if (isset($_POST['ids'])) {
                $ids = $_POST['ids'];
                foreach ($ids as $id) {
                    $nombrearc = $conn->query("SELECT nomArchivo FROM archivos WHERE id = $id");
                    $nombrearc = $nombrearc->fetch_assoc();
                    $nombrearc = $nombrearc['nomArchivo'];

                    $stmt = $conn->prepare("DELETE FROM archivos WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                    $stmt->close();
                    $mensajes[] = "Archivo eliminado con éxito!";

                    // Registrar log de eliminación de archivo
                    registrar_log($conn, $usuario, 'Eliminación de archivo', "Archivo '$nombrearc' eliminado.");

                } 
            // Redirigir para evitar reenvío de formulario
            header('Location: adminArch.php');
            exit;
            } else {
                echo "<script>alert('No se ha seleccionado ningún archivo para eliminar.');</script>";
            }
        } elseif (isset($_POST['eliminar_individual'])) {
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception("No se ha especificado un ID de archivo a eliminar.");
            }
            $id = $_POST['id'];
            
            $nombrearci = $conn->query("SELECT nomArchivo FROM archivos WHERE id = $id");
            $nombrearci = $nombrearci->fetch_assoc();
            $nombrearci = $nombre['nomArchivo'];
            
            $stmt = $conn->prepare("DELETE FROM archivos WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $mensajes[] = "Archivo eliminado con éxito!";

            // Registrar log de eliminación de archivo
            registrar_log($conn, $usuario, 'Eliminación de archivo', "Archivo '$nombrearci' eliminado.");

            // Redirigir para evitar reenvío de formulario
            header('Location: adminArch.php');
            exit;
        }
    }
    
    // Obtener todos los archivos
    $sort = $_GET['sort'] ?? 'nomArchivo';
    $order = $_GET['order'] ?? 'asc';

    // Validación de entradas para evitar SQL Injection
    $allowed_sort = ['nomArchivo', 'fecha', 'tamano'];
    $allowed_order = ['asc', 'desc'];

    if (!in_array($sort, $allowed_sort)) $sort = 'nomArchivo';
    if (!in_array($order, $allowed_order)) $order = 'asc';

    $query = "SELECT * FROM archivos ORDER BY $sort $order";
    $result = $conn->query($query);
    $row = [];
    while ($archivo = $result->fetch_assoc()) {
        $row[] = $archivo;
    }

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'MySQL server has gone away') !== false) {
        $mensajes[] = 'Error: La conexión con la base de datos se ha perdido.';
    } else {
        $mensajes[] = 'Error: ' . $e->getMessage();
    }
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
    // Mostrar todos los mensajes de alerta juntos
    if (!empty($mensajes)) {
        echo "<script>alert('" . implode("\\n", $mensajes) . "');</script>";
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
    <?php if ($_SESSION['rol'] === 1): ?>
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
                <p class="max-size-warning">Tamaño máximo de archivo: 10MB</p>
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
                    <th>Nombre<a href="?sort=nomArchivo&order=asc" class="ordenar">▲</a> <a href="?sort=nomArchivo&order=desc" class="ordenar">▼</a></th>
                    <th>Fecha<a href="?sort=fecha&order=asc" class="ordenar">▲</a> <a href="?sort=fecha&order=desc" class="ordenar">▼</a></th>
                    <th>Tamaño<a href="?sort=tamano&order=asc" class="ordenar">▲</a> <a href="?sort=tamano&order=desc" class="ordenar">▼</a></th>
                </tr>
            </thead>
            <tbody id="cuerpoTabla">
            <?php foreach ($row as $archivo): ?>
                    <tr id="row-<?= $archivo['id'] ?>">
                        <td class="check"><input type="checkbox" name="ids[]" value="<?= $archivo['id'] ?>"></td>
                        <td class="botonesfile">
                            <a class="verbtn" href="verArchivo.php?id=<?= $archivo['id']; ?>" target="_blank"><i class="material-icons">import_contacts</i></a>
                            <?php if ($_SESSION['rol'] === 1): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $archivo['id']; ?>">
                                    <button class="verelbtn" type="submit" name="eliminar_individual"><i class="material-icons">delete_sweep</i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td><?= $archivo['nomArchivo'] ?></td>
                        <td><?= $archivo['fecha'] ?></td>
                        <td><?= $archivo['tamano'] . ' ' . 'KB' ?></td>
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