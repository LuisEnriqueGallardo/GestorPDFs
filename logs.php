<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit;
}

include("basedatos.php");

$sql = 'SELECT * FROM logs';
$result = $conn->query($sql);

$rows = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Logs</title>
    <link rel="stylesheet" href="assets/css/logs.css">
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
        <h1>Registros
            <span>Aqui se encuentran los registros del sistema.</span>
        </h1>
    </div>
    <table border="1">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody id="cuerpoTabla">
            <?php foreach ($rows as $row): ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td><?= htmlspecialchars($row['accion']) ?></td>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td class="findocs" colspan="5">Fin de los registros.</td>
            </tr>
        </tbody>
    </table>
</body>
</html>