<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.html");
    exit();
}
include("conexion.php");

$nombre = trim($_POST['nombre']);
$precio = floatval($_POST['precio']);
$stock = intval($_POST['stock']);

// Validaciones
if(empty($nombre)) {
    header("Location: dashboard.php?error=nombre_vacio");
    exit();
}
if($precio <= 0) {
    header("Location: dashboard.php?error=precio_invalido");
    exit();
}
if($stock < 0) {
    header("Location: dashboard.php?error=stock_invalido");
    exit();
}

// Usar prepared statement
$stmt = $conexion->prepare("INSERT INTO productos(nombre, precio, stock) VALUES (?, ?, ?)");
$stmt->bind_param("sdi", $nombre, $precio, $stock);
$stmt->execute();
$stmt->close();

header("Location: dashboard.php?mensaje=agregado");
?>