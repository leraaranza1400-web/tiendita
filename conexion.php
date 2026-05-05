<?php
$conexion = new mysqli("localhost", "tiendita_user", "Tiendita123!", "tiendita_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>
