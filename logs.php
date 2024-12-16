<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'sistemapdf');
if ($conn->connect_error) {
    die('Error de conexión: ' . $conn->connect_error);
}

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
                <li><a href="adminUsuarios.php" id="admin-link">Usuarios</a></li>
                <li><a href="logs.php" id="arch-link">Registro</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>
    <div class="subtitulo">
        <h1>Documentos
            <span>Documentos disponibles sobre residencias.</span>
        </h1>
    </div>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Descripción</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['usuario']) ?></td>
                        <td><?= htmlspecialchars($row['accion']) ?></td>
                        <td><?= htmlspecialchars($row['descripcion']) ?></td>
                        <td><?= htmlspecialchars($row['fecha']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No hay registros de logs.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>