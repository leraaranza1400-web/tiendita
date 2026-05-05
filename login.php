<?php
session_start();
include("conexion.php");

$correo = $_POST['correo'];
$password = $_POST['password'];

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo=? AND password=?");
$stmt->bind_param("ss", $correo, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    
    // === CREAR COOKIES ===
    // Cookie para el correo (dura 7 días)
    setcookie("user_email", $correo, time() + (86400 * 7), "/");
    
    // Cookie para recordar sesión (dura 7 días)
    setcookie("user_logged", "true", time() + (86400 * 7), "/");
    
    // Cookie para el nombre del usuario (si tienes campo nombre)
    setcookie("user_name", $usuario['nombre'] ?? $correo, time() + (86400 * 7), "/");
    
    // Sesión normal de PHP
    $_SESSION['usuario'] = $correo;
    $_SESSION['id_usuario'] = $usuario['id'];
    
    header("Location: dashboard.php");
} else {
    echo "<script>alert(' Correo o contraseña incorrectos'); window.location='index.html';</script>";
}
$stmt->close();
?>
