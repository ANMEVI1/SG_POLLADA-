<?php
/**
 * API - Módulo de Inversión
 * Acciones: add_producto, edit_producto, delete_producto, toggle_comprado,
 *           add_egreso, edit_egreso, delete_egreso, get_totales
 */

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ─── PRODUCTOS ──────────────────────────────────────────
    case 'add_producto':
        $stmt = $pdo->prepare("INSERT INTO producto (categoria_id, nombre, cantidad, kilo, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
        $cantidad = $_POST['cantidad'] !== '' ? $_POST['cantidad'] : null;
        $kilo = $_POST['kilo'] !== '' ? $_POST['kilo'] : null;
        $precio = floatval($_POST['precio_unitario']);
        $factor = $cantidad ?: ($kilo ?: 1);
        $subtotal = round($factor * $precio, 2);
        $stmt->execute([
            $_POST['categoria_id'],
            trim($_POST['nombre']),
            $cantidad,
            $kilo,
            $precio,
            $subtotal
        ]);
        echo json_encode(['success' => true, 'message' => 'Producto agregado']);
        break;

    case 'edit_producto':
        $cantidad = $_POST['cantidad'] !== '' ? $_POST['cantidad'] : null;
        $kilo = $_POST['kilo'] !== '' ? $_POST['kilo'] : null;
        $precio = floatval($_POST['precio_unitario']);
        $factor = $cantidad ?: ($kilo ?: 1);
        $subtotal = round($factor * $precio, 2);
        $stmt = $pdo->prepare("UPDATE producto SET categoria_id=?, nombre=?, cantidad=?, kilo=?, precio_unitario=?, subtotal=? WHERE id=?");
        $stmt->execute([
            $_POST['categoria_id'],
            trim($_POST['nombre']),
            $cantidad,
            $kilo,
            $precio,
            $subtotal,
            $_POST['producto_id']
        ]);
        echo json_encode(['success' => true, 'message' => 'Producto actualizado']);
        break;

    case 'delete_producto':
        $stmt = $pdo->prepare("DELETE FROM producto WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Producto eliminado']);
        break;

    case 'toggle_comprado':
        $stmt = $pdo->prepare("UPDATE producto SET comprado = NOT comprado, fecha_compra = IF(comprado = 0, CURDATE(), NULL) WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $stmt = $pdo->prepare("SELECT comprado FROM producto WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $row = $stmt->fetch();
        echo json_encode(['success' => true, 'comprado' => $row['comprado'], 'message' => $row['comprado'] ? 'Marcado como comprado' : 'Desmarcado']);
        break;

    // ─── EGRESOS ────────────────────────────────────────────
    case 'add_egreso':
        $stmt = $pdo->prepare("INSERT INTO egreso_imprevisto (descripcion, categoria_id, monto, fecha, salio_de_caja) VALUES (?, ?, ?, ?, ?)");
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
        $salio_caja = isset($_POST['salio_de_caja']) ? 1 : 0;
        $stmt->execute([
            trim($_POST['descripcion']),
            $_POST['categoria_id'],
            floatval($_POST['monto']),
            $fecha,
            $salio_caja
        ]);
        echo json_encode(['success' => true, 'message' => 'Egreso agregado']);
        break;

    case 'edit_egreso':
        $fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : null;
        $salio_caja = isset($_POST['salio_de_caja']) ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE egreso_imprevisto SET descripcion=?, categoria_id=?, monto=?, fecha=?, salio_de_caja=? WHERE id=?");
        $stmt->execute([
            trim($_POST['descripcion']),
            $_POST['categoria_id'],
            floatval($_POST['monto']),
            $fecha,
            $salio_caja,
            $_POST['egreso_id']
        ]);
        echo json_encode(['success' => true, 'message' => 'Egreso actualizado']);
        break;

    case 'delete_egreso':
        $stmt = $pdo->prepare("DELETE FROM egreso_imprevisto WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Egreso eliminado']);
        break;

    // ─── TOTALES ────────────────────────────────────────────
    case 'get_totales':
        $totalProd = $pdo->query("SELECT COALESCE(SUM(subtotal), 0) AS total FROM producto WHERE comprado = 1")->fetch()['total'];
        $totalEgr = $pdo->query("SELECT COALESCE(SUM(monto), 0) AS total FROM egreso_imprevisto")->fetch()['total'];
        echo json_encode([
            'success' => true,
            'data' => [
                'total_productos' => $totalProd,
                'total_egresos' => $totalEgr,
                'gran_total' => round($totalProd + $totalEgr, 2)
            ]
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
