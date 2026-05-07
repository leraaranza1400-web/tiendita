<?php
// ============================================================
//  api/ventas.php — Registro y consulta de Ventas
// ============================================================
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';

requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = getDB();

// ── GET: Listar ventas ─────────────────────────────────────
if ($method === 'GET') {
    $limit  = min((int)($_GET['limit'] ?? 50), 200);
    $offset = (int)($_GET['offset'] ?? 0);

    // Empleados solo ven sus propias ventas; admin ve todas
    $whereUser = isAdmin() ? '' : ' AND v.id_usuario = ' . (int)$_SESSION['user_id'];

    $stmt = $db->prepare(
        "SELECT v.id_venta, v.folio, v.total, v.metodo_pago, v.estado,
                v.created_at AS fecha,
                CONCAT(c.nombre,' ',c.apellido) AS cliente,
                CONCAT(u.nombre,' ',u.apellido) AS empleado
         FROM ventas v
         JOIN clientes c ON c.id_cliente = v.id_cliente
         JOIN usuarios u ON u.id_usuario = v.id_usuario
         WHERE 1=1 $whereUser
         ORDER BY v.created_at DESC
         LIMIT $limit OFFSET $offset"
    );
    $stmt->execute();
    jsonResponse($stmt->fetchAll());
}

// ── POST: Registrar venta ──────────────────────────────────
if ($method === 'POST' && $action === 'registrar') {
    $data = json_decode(file_get_contents('php://input'), true);

    $id_cliente  = (int)($data['id_cliente']  ?? 0);
    $metodo_pago = $data['metodo_pago'] ?? 'Efectivo';
    $notas       = trim($data['notas'] ?? '');
    $items       = $data['items'] ?? [];   // [{id_producto, cantidad}, ...]

    // Validaciones
    if ($id_cliente <= 0) jsonResponse(['ok'=>false,'msg'=>'Cliente requerido.'],422);
    if (empty($items))    jsonResponse(['ok'=>false,'msg'=>'Agrega al menos un producto.'],422);

    $metodos_validos = ['Efectivo','Tarjeta','Transferencia','Otro'];
    if (!in_array($metodo_pago, $metodos_validos)) {
        jsonResponse(['ok'=>false,'msg'=>'Método de pago inválido.'],422);
    }

    $db->beginTransaction();
    try {
        $subtotal_venta = 0;

        // Validar stock y calcular subtotales
        $lineas = [];
        foreach ($items as $item) {
            $id_p = (int)($item['id_producto'] ?? 0);
            $qty  = (int)($item['cantidad']    ?? 0);
            if ($id_p <= 0 || $qty <= 0) continue;

            $pStmt = $db->prepare(
                "SELECT id_producto, nombre, precio, stock
                 FROM productos WHERE id_producto = ? AND activo = 1 FOR UPDATE"
            );
            $pStmt->execute([$id_p]);
            $prod = $pStmt->fetch();

            if (!$prod) {
                throw new RuntimeException("Producto ID $id_p no encontrado.");
            }
            if ($prod['stock'] < $qty) {
                throw new RuntimeException(
                    "Stock insuficiente para '{$prod['nombre']}'. Disponible: {$prod['stock']}."
                );
            }

            $sub = round($prod['precio'] * $qty, 2);
            $subtotal_venta += $sub;
            $lineas[] = [
                'id_producto' => $id_p,
                'cantidad'    => $qty,
                'precio_unit' => $prod['precio'],
                'subtotal'    => $sub,
            ];
        }

        if (empty($lineas)) {
            throw new RuntimeException('No se encontraron productos válidos.');
        }

        $folio = generarFolio($db);

        // Insertar venta
        $vStmt = $db->prepare(
            "INSERT INTO ventas (folio, id_cliente, id_usuario, subtotal, total, metodo_pago, notas)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $vStmt->execute([
            $folio,
            $id_cliente,
            $_SESSION['user_id'],
            $subtotal_venta,
            $subtotal_venta,   // sin descuento por ahora
            $metodo_pago,
            $notas,
        ]);
        $id_venta = (int)$db->lastInsertId();

        // Insertar detalles y descontar stock
        $dStmt = $db->prepare(
            "INSERT INTO detalles_venta (id_venta, id_producto, cantidad, precio_unit, subtotal)
             VALUES (?, ?, ?, ?, ?)"
        );
        $uStmt = $db->prepare(
            "UPDATE productos SET stock = stock - ? WHERE id_producto = ?"
        );

        foreach ($lineas as $l) {
            $dStmt->execute([$id_venta, $l['id_producto'], $l['cantidad'], $l['precio_unit'], $l['subtotal']]);
            $uStmt->execute([$l['cantidad'], $l['id_producto']]);
        }

        $db->commit();
        jsonResponse(['ok' => true, 'msg' => "Venta $folio registrada correctamente.", 'folio' => $folio, 'id_venta' => $id_venta, 'total' => $subtotal_venta]);

    } catch (RuntimeException $e) {
        $db->rollBack();
        jsonResponse(['ok' => false, 'msg' => $e->getMessage()], 422);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['ok' => false, 'msg' => 'Error interno al procesar la venta.'], 500);
    }
}
