<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
include("conexion.php");

$id = intval($_GET['id']);

$stmt = $conexion->prepare("DELETE FROM productos WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: dashboard.php");
?>