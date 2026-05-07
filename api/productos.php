<?php
// ============================================================
//  api/productos.php — CRUD completo de Productos (JSON API)
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

// ── GET: Listar / Buscar productos ─────────────────────────
if ($method === 'GET') {
    $search = trim($_GET['q'] ?? '');
    $cat    = (int)($_GET['categoria'] ?? 0);
    $id     = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        // Producto individual
        $stmt = $db->prepare(
            "SELECT p.*, c.nombre AS categoria
             FROM productos p
             JOIN categorias c ON c.id_categoria = p.id_categoria
             WHERE p.id_producto = ?"
        );
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
        jsonResponse($producto ?: ['error' => 'Producto no encontrado'], $producto ? 200 : 404);
    }

    $sql    = "SELECT p.id_producto, p.codigo, p.nombre, p.precio, p.stock,
                      p.stock_minimo, p.activo, c.nombre AS categoria, p.id_categoria
               FROM productos p
               JOIN categorias c ON c.id_categoria = p.id_categoria
               WHERE 1=1";
    $params = [];

    if ($search) {
        $sql   .= " AND (p.nombre LIKE ? OR p.codigo LIKE ?)";
        $like   = "%$search%";
        $params[] = $like;
        $params[] = $like;
    }
    if ($cat > 0) {
        $sql   .= " AND p.id_categoria = ?";
        $params[] = $cat;
    }
    $sql .= " ORDER BY p.nombre ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    jsonResponse($stmt->fetchAll());
}

// ── POST: Crear / Actualizar / Eliminar ────────────────────
if ($method === 'POST') {
    requireAdmin(); // Solo admin puede modificar catálogo

    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    switch ($action) {
        // ── CREAR ────────────────────────────────────────
        case 'crear':
            $campos = ['codigo','nombre','precio','stock','stock_minimo','id_categoria'];
            foreach ($campos as $c) {
                if (!isset($data[$c]) || $data[$c] === '') {
                    jsonResponse(['ok' => false, 'msg' => "El campo '$c' es requerido."], 422);
                }
            }
            if ((float)$data['precio'] < 0) {
                jsonResponse(['ok' => false, 'msg' => 'El precio no puede ser negativo.'], 422);
            }

            // Verificar código único
            $ck = $db->prepare("SELECT id_producto FROM productos WHERE codigo = ?");
            $ck->execute([strtoupper(trim($data['codigo']))]);
            if ($ck->fetch()) {
                jsonResponse(['ok' => false, 'msg' => 'El código de producto ya existe.'], 409);
            }

            $stmt = $db->prepare(
                "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, stock_minimo, id_categoria)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                strtoupper(trim($data['codigo'])),
                trim($data['nombre']),
                trim($data['descripcion'] ?? ''),
                (float)$data['precio'],
                (int)$data['stock'],
                (int)$data['stock_minimo'],
                (int)$data['id_categoria'],
            ]);
            jsonResponse(['ok' => true, 'msg' => 'Producto creado correctamente.', 'id' => $db->lastInsertId()]);
            break;

        // ── EDITAR ────────────────────────────────────────
        case 'editar':
            $id = (int)($data['id_producto'] ?? 0);
            if (!$id) jsonResponse(['ok' => false, 'msg' => 'ID inválido.'], 422);

            $stmt = $db->prepare(
                "UPDATE productos
                 SET codigo=?, nombre=?, descripcion=?, precio=?,
                     stock=?, stock_minimo=?, id_categoria=?, activo=?
                 WHERE id_producto=?"
            );
            $stmt->execute([
                strtoupper(trim($data['codigo'])),
                trim($data['nombre']),
                trim($data['descripcion'] ?? ''),
                (float)$data['precio'],
                (int)$data['stock'],
                (int)$data['stock_minimo'],
                (int)$data['id_categoria'],
                (int)($data['activo'] ?? 1),
                $id,
            ]);
            jsonResponse(['ok' => true, 'msg' => 'Producto actualizado.']);
            break;

        // ── ELIMINAR (soft delete) ────────────────────────
        case 'eliminar':
            $id = (int)($data['id_producto'] ?? 0);
            if (!$id) jsonResponse(['ok' => false, 'msg' => 'ID inválido.'], 422);

            $stmt = $db->prepare("UPDATE productos SET activo=0 WHERE id_producto=?");
            $stmt->execute([$id]);
            jsonResponse(['ok' => true, 'msg' => 'Producto eliminado.']);
            break;

        default:
            jsonResponse(['ok' => false, 'msg' => 'Acción no válida.'], 400);
    }
}
