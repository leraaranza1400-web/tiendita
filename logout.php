<?php
session_start();

// === ELIMINAR COOKIES ===
setcookie("user_email", "", time() - 3600, "/");
setcookie("user_logged", "", time() - 3600, "/");
setcookie("user_name", "", time() - 3600, "/");

// Destruir sesión
session_destroy();

header("Location: index.html");
?>
