<?php
// ============================================================
//  auth/auth.php — Lógica de Login, Registro y Logout
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ── LOGIN ──────────────────────────────────────────────
    case 'login':
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            jsonResponse(['ok' => false, 'msg' => 'Usuario y contraseña son requeridos.'], 422);
        }

        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.username,
                    u.password, u.id_rol, u.activo, r.nombre AS rol_nombre
             FROM usuarios u
             JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.username = ? OR u.email = ?
             LIMIT 1"
        );
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !$user['activo']) {
            jsonResponse(['ok' => false, 'msg' => 'Credenciales inválidas o cuenta inactiva.'], 401);
        }

        if (!password_verify($password, $user['password'])) {
            jsonResponse(['ok' => false, 'msg' => 'Credenciales inválidas.'], 401);
        }

        // Regenerar ID de sesión al autenticarse
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id_usuario'];
        $_SESSION['nombre']     = $user['nombre'] . ' ' . $user['apellido'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['rol_id']     = (int)$user['id_rol'];
        $_SESSION['rol_nombre'] = $user['rol_nombre'];

        jsonResponse(['ok' => true, 'msg' => '¡Bienvenido, ' . $user['nombre'] . '!', 'redirect' => 'panel.php']);
        break;

    // ── REGISTRO ───────────────────────────────────────────
    case 'register':
        $nombre   = trim($_POST['nombre']   ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['confirm']       ?? '';
        // Rol: admin puede asignar cualquiera; registro público siempre = Empleado
        $id_rol   = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] === 1
                    ? (int)($_POST['id_rol'] ?? 2)
                    : 2;

        // Validaciones básicas
        if (!$nombre || !$apellido || !$email || !$username || !$password) {
            jsonResponse(['ok' => false, 'msg' => 'Todos los campos son obligatorios.'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['ok' => false, 'msg' => 'El correo electrónico no es válido.'], 422);
        }
        if (strlen($password) < 6) {
            jsonResponse(['ok' => false, 'msg' => 'La contraseña debe tener al menos 6 caracteres.'], 422);
        }
        if ($password !== $confirm) {
            jsonResponse(['ok' => false, 'msg' => 'Las contraseñas no coinciden.'], 422);
        }

        $db = getDB();

        // Verificar duplicados
        $check = $db->prepare("SELECT id_usuario FROM usuarios WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            jsonResponse(['ok' => false, 'msg' => 'El usuario o correo ya está registrado.'], 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $ins  = $db->prepare(
            "INSERT INTO usuarios (nombre, apellido, email, username, password, id_rol)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ins->execute([$nombre, $apellido, $email, $username, $hash, $id_rol]);

        jsonResponse(['ok' => true, 'msg' => 'Usuario registrado correctamente.', 'redirect' => 'index.php']);
        break;

    // ── LOGOUT ─────────────────────────────────────────────
    case 'logout':
        session_unset();
        session_destroy();
        header('Location: ../index.php?msg=sesion_cerrada');
        exit;

    default:
        jsonResponse(['ok' => false, 'msg' => 'Acción no reconocida.'], 400);
}
