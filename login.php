<?php
session_start();
include("conexion.php");

$correo   = trim($_POST['correo']   ?? '');
$password = trim($_POST['password'] ?? '');

if(empty($correo) || empty($password)) {
    echo "<script>alert('⚠️ Completa todos los campos'); window.location='index.html';</script>";
    exit();
}

// Buscar usuario solo por correo
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();

    // Verificar contraseña con password_verify (compatible con password_hash)
    if(password_verify($password, $usuario['password'])) {

        // === CREAR COOKIES ===
        setcookie("user_email", $correo,                   time() + (86400 * 7), "/");
        setcookie("user_logged", "true",                   time() + (86400 * 7), "/");
        setcookie("user_name",  $usuario['nombre'] ?? $correo, time() + (86400 * 7), "/");

        // Sesión PHP
        $_SESSION['usuario']    = $correo;
        $_SESSION['id_usuario'] = $usuario['id'];

        $stmt->close();
        header("Location: dashboard.php");
        exit();
    }
}

$stmt->close();
echo "<script>alert('❌ Correo o contraseña incorrectos'); window.location='index.html';</script>";
?>
