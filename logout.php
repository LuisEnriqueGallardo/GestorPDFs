<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit;
}

include("basedatos.php");

registrar_log($conn, $_SESSION['usuario'], 'Salida de sesión', 'Sesión cerrada.');
session_destroy();
header('Location: login.php');
exit();
?>
