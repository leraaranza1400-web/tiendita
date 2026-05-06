<?php
session_start();
include("conexion.php");

// Recoger y limpiar datos
$nombre   = trim($_POST['nombre']   ?? '');
$correo   = trim($_POST['correo']   ?? '');
$password = trim($_POST['password'] ?? '');
$password2= trim($_POST['password2']?? '');

// Validaciones
if(empty($nombre) || empty($correo) || empty($password) || empty($password2)) {
    header("Location: registro.php?error=campos_vacios");
    exit();
}

if(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    header("Location: registro.php?error=email_invalido");
    exit();
}

if(strlen($password) < 6) {
    header("Location: registro.php?error=pass_corta");
    exit();
}

if($password !== $password2) {
    header("Location: registro.php?error=pass_no_coincide");
    exit();
}

// Verificar si el correo ya existe
$check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
$check->store_result();

if($check->num_rows > 0) {
    $check->close();
    header("Location: registro.php?error=email_existe");
    exit();
}
$check->close();

// Hashear contraseña y guardar usuario
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conexion->prepare("INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $correo, $hash);
$stmt->execute();
$stmt->close();

// Redirigir al login con mensaje de éxito
header("Location: index.html?registro=exitoso");
exit();
?>
