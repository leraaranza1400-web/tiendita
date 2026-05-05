<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
include("conexion.php");

$id = intval($_POST['id']);
$nombre = trim($_POST['nombre']);
$precio = floatval($_POST['precio']);
$stock = intval($_POST['stock']);

if($id > 0 && !empty($nombre) && $precio > 0 && $stock >= 0) {
    $stmt = $conexion->prepare("UPDATE productos SET nombre=?, precio=?, stock=? WHERE id=?");
    $stmt->bind_param("sdii", $nombre, $precio, $stock, $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: dashboard.php?mensaje=actualizado");
?>