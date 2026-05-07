<?php
// ============================================================
//  api/usuarios.php — CRUD de Usuarios
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

if ($method === 'GET') {
    requireAdmin();
    $stmt = $db->query(
        "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.username,
                u.id_rol, r.nombre AS rol, u.activo, u.created_at
         FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol
         ORDER BY u.created_at DESC"
    );
    jsonResponse($stmt->fetchAll());
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $id   = (int)($data['id_usuario'] ?? 0);

    // Empleado solo puede editar su propio perfil
    if (!isAdmin() && ($action !== 'editar' || $id !== (int)$_SESSION['user_id'])) {
        jsonResponse(['ok'=>false,'msg'=>'Acceso denegado.'],403);
    }

    switch ($action) {
        case 'editar':
            if (!$id) jsonResponse(['ok'=>false,'msg'=>'ID requerido.'],422);
            $campos = [];
            $vals   = [];
            if (!empty($data['nombre']))   { $campos[] = 'nombre=?';   $vals[] = trim($data['nombre']); }
            if (!empty($data['apellido'])) { $campos[] = 'apellido=?'; $vals[] = trim($data['apellido']); }
            if (!empty($data['email']))    { $campos[] = 'email=?';    $vals[] = trim($data['email']); }
            if (isAdmin() && isset($data['id_rol'])) { $campos[] = 'id_rol=?'; $vals[] = (int)$data['id_rol']; }
            if (isAdmin() && isset($data['activo'])) { $campos[] = 'activo=?'; $vals[] = (int)$data['activo']; }
            if (!empty($data['password'])) {
                $campos[] = 'password=?';
                $vals[]   = password_hash($data['password'], PASSWORD_BCRYPT, ['cost'=>12]);
            }
            if (empty($campos)) jsonResponse(['ok'=>false,'msg'=>'Nada que actualizar.'],422);
            $vals[] = $id;
            $db->prepare("UPDATE usuarios SET ".implode(',',$campos)." WHERE id_usuario=?")->execute($vals);
            jsonResponse(['ok'=>true,'msg'=>'Usuario actualizado.']);
            break;

        case 'eliminar':
            requireAdmin();
            if (!$id) jsonResponse(['ok'=>false,'msg'=>'ID requerido.'],422);
            if ($id === (int)$_SESSION['user_id']) jsonResponse(['ok'=>false,'msg'=>'No puedes eliminarte a ti mismo.'],422);
            $db->prepare("UPDATE usuarios SET activo=0 WHERE id_usuario=?")->execute([$id]);
            jsonResponse(['ok'=>true,'msg'=>'Usuario desactivado.']);
            break;

        default:
            jsonResponse(['ok'=>false,'msg'=>'Acción inválida.'],400);
    }
}

// ============================================================
//  api/clientes.php — CRUD de Clientes
// ============================================================
