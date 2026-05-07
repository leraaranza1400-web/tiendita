<?php
// ============================================================
//  api/clientes.php — CRUD de Clientes
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

if ($method === 'GET') {
    $search = trim($_GET['q'] ?? '');
    $id     = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
        $stmt->execute([$id]);
        jsonResponse($stmt->fetch() ?: ['error'=>'No encontrado'], $stmt->rowCount() ? 200 : 404);
    }

    $sql = "SELECT * FROM clientes WHERE 1=1";
    $params = [];
    if ($search) {
        $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR telefono LIKE ?)";
        $l = "%$search%";
        $params = [$l,$l,$l,$l];
    }
    $sql .= " ORDER BY nombre ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse($stmt->fetchAll());
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    switch ($action) {
        case 'crear':
            if (empty($data['nombre']) || empty($data['apellido'])) {
                jsonResponse(['ok'=>false,'msg'=>'Nombre y apellido son requeridos.'],422);
            }
            $stmt = $db->prepare(
                "INSERT INTO clientes (nombre,apellido,email,telefono,direccion,rfc)
                 VALUES (?,?,?,?,?,?)"
            );
            $stmt->execute([
                trim($data['nombre']),trim($data['apellido']),
                trim($data['email']??'')??null, trim($data['telefono']??'')??null,
                trim($data['direccion']??'')??null, trim($data['rfc']??'')??null,
            ]);
            jsonResponse(['ok'=>true,'msg'=>'Cliente registrado.','id'=>$db->lastInsertId()]);
            break;

        case 'editar':
            $id = (int)($data['id_cliente']??0);
            if (!$id) jsonResponse(['ok'=>false,'msg'=>'ID requerido.'],422);
            // Solo admin puede editar clientes
            requireAdmin();
            $stmt = $db->prepare(
                "UPDATE clientes SET nombre=?,apellido=?,email=?,telefono=?,direccion=?,rfc=?
                 WHERE id_cliente=?"
            );
            $stmt->execute([
                trim($data['nombre']),trim($data['apellido']),
                trim($data['email']??'')??null, trim($data['telefono']??'')??null,
                trim($data['direccion']??'')??null, trim($data['rfc']??'')??null, $id
            ]);
            jsonResponse(['ok'=>true,'msg'=>'Cliente actualizado.']);
            break;

        default:
            jsonResponse(['ok'=>false,'msg'=>'Acción inválida.'],400);
    }
}
