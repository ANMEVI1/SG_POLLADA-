<?php
/**
 * API - Módulo de Entrega y Ventas
 * Acciones: add_cliente, edit_cliente, delete_cliente, next_code,
 *           generar_entregas, toggle_entregado, toggle_arroz, toggle_pago, get_stats
 */

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ─── CLIENTES ───────────────────────────────────────────
    case 'add_cliente':
        $codigo = trim($_POST['codigo_4digitos']);
        // Verificar código único
        $check = $pdo->prepare("SELECT COUNT(*) FROM cliente WHERE codigo_4digitos = ?");
        $check->execute([$codigo]);
        if ($check->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'El código ' . $codigo . ' ya existe']);
            break;
        }
        $stmt = $pdo->prepare("INSERT INTO cliente (nombre, direccion, codigo_4digitos, zona_entrega_id) VALUES (?, ?, ?, ?)");
        $zonaId = !empty($_POST['zona_entrega_id']) ? $_POST['zona_entrega_id'] : null;
        $stmt->execute([
            trim($_POST['nombre']),
            trim($_POST['direccion']),
            $codigo,
            $zonaId
        ]);
        echo json_encode(['success' => true, 'message' => 'Cliente registrado', 'id' => $pdo->lastInsertId()]);
        break;

    case 'edit_cliente':
        $zonaId = !empty($_POST['zona_entrega_id']) ? $_POST['zona_entrega_id'] : null;
        $stmt = $pdo->prepare("UPDATE cliente SET nombre=?, direccion=?, codigo_4digitos=?, zona_entrega_id=? WHERE id=?");
        $stmt->execute([
            trim($_POST['nombre']),
            trim($_POST['direccion']),
            trim($_POST['codigo_4digitos']),
            $zonaId,
            $_POST['cliente_id']
        ]);
        // Also update zona in entrega_cliente
        $pdo->prepare("UPDATE entrega_cliente SET zona_entrega_id=? WHERE cliente_id=?")->execute([$zonaId, $_POST['cliente_id']]);
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado']);
        break;

    case 'delete_cliente':
        $pdo->prepare("DELETE FROM cliente WHERE id = ?")->execute([$_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado']);
        break;

    case 'next_code':
        $inicio = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'codigo_cliente_inicio'")->fetchColumn() ?: '0347';
        $maxCode = $pdo->query("SELECT MAX(CAST(codigo_4digitos AS UNSIGNED)) FROM cliente")->fetchColumn();
        $nextNum = max(intval($inicio), intval($maxCode) + 1);
        echo json_encode(['success' => true, 'code' => str_pad($nextNum, 4, '0', STR_PAD_LEFT)]);
        break;

    // ─── ENTREGAS ───────────────────────────────────────────
    case 'generar_entregas':
        // Get default platillo
        $platillo = $pdo->query("SELECT id FROM platillo WHERE estado = 'activo' LIMIT 1")->fetch();
        if (!$platillo) {
            echo json_encode(['success' => false, 'message' => 'No hay platillo activo']);
            break;
        }
        // Get clientes without entrega
        $stmt = $pdo->query("
            SELECT c.id, c.zona_entrega_id 
            FROM cliente c 
            LEFT JOIN entrega_cliente ec ON c.id = ec.cliente_id 
            WHERE ec.id IS NULL
        ");
        $clientes = $stmt->fetchAll();
        $count = 0;
        $insert = $pdo->prepare("INSERT INTO entrega_cliente (cliente_id, platillo_id, zona_entrega_id) VALUES (?, ?, ?)");
        foreach ($clientes as $c) {
            $insert->execute([$c['id'], $platillo['id'], $c['zona_entrega_id']]);
            $count++;
        }
        echo json_encode(['success' => true, 'message' => $count . ' entregas generadas']);
        break;

    case 'toggle_entregado':
        $id = $_POST['id'];
        // Get current state
        $stmt = $pdo->prepare("SELECT ec.*, p.precio FROM entrega_cliente ec JOIN platillo p ON ec.platillo_id = p.id WHERE ec.id = ?");
        $stmt->execute([$id]);
        $entrega = $stmt->fetch();
        
        if (!$entrega) {
            echo json_encode(['success' => false, 'message' => 'Entrega no encontrada']);
            break;
        }
        
        $newState = $entrega['entregado'] ? 0 : 1;
        
        if ($newState == 1) {
            // Marcar como entregado -> crear venta
            $pdo->prepare("UPDATE entrega_cliente SET entregado = 1, hora_entrega = NOW() WHERE id = ?")->execute([$id]);
            // Check if venta already exists
            $existingVenta = $pdo->prepare("SELECT id FROM venta WHERE entrega_cliente_id = ?");
            $existingVenta->execute([$id]);
            if (!$existingVenta->fetch()) {
                $pdo->prepare("INSERT INTO venta (entrega_cliente_id, cliente_id, monto, estado_pago) VALUES (?, ?, ?, 'pendiente')")
                    ->execute([$id, $entrega['cliente_id'], $entrega['precio']]);
            }
        } else {
            // Desmarcar -> requiere PIN
            $pinInput = $_POST['pin'] ?? '';
            $pinReal = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'pin_cierre'")->fetchColumn();
            if ($pinInput !== $pinReal) {
                echo json_encode(['success' => false, 'message' => 'PIN incorrecto o requerido para desmarcar']);
                exit;
            }
            
            // Desmarcar -> eliminar venta
            $pdo->prepare("UPDATE entrega_cliente SET entregado = 0, hora_entrega = NULL WHERE id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM venta WHERE entrega_cliente_id = ?")->execute([$id]);
        }
        
        echo json_encode(['success' => true, 'entregado' => $newState, 'message' => $newState ? '✓ Entregado' : 'Entrega revertida']);
        break;

    case 'toggle_arroz':
        $id = $_POST['id'];
        $pdo->prepare("UPDATE entrega_cliente SET con_arroz = NOT con_arroz WHERE id = ?")->execute([$id]);
        $stmt = $pdo->prepare("SELECT con_arroz FROM entrega_cliente WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'con_arroz' => $stmt->fetchColumn()]);
        break;

    case 'toggle_pago':
        $id = $_POST['id'];
        $stmt = $pdo->prepare("SELECT id, estado_pago FROM venta WHERE entrega_cliente_id = ?");
        $stmt->execute([$id]);
        $venta = $stmt->fetch();
        
        if (!$venta) {
            echo json_encode(['success' => false, 'message' => 'Primero marca como entregado']);
            break;
        }
        
        // Ciclo: pendiente -> efectivo -> yape -> pendiente
        $newState = 'efectivo';
        if ($venta['estado_pago'] === 'efectivo') $newState = 'yape';
        if ($venta['estado_pago'] === 'yape') $newState = 'pendiente';
        
        // Si va a revertir a pendiente, requiere PIN
        if ($newState === 'pendiente') {
            $pinInput = $_POST['pin'] ?? '';
            $pinReal = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'pin_cierre'")->fetchColumn();
            if ($pinInput !== $pinReal) {
                echo json_encode(['success' => false, 'message' => 'PIN requerido para anular pago']);
                exit;
            }
        }
        
        $pdo->prepare("UPDATE venta SET estado_pago = ? WHERE id = ?")->execute([$newState, $venta['id']]);
        
        $msg = 'Pendiente';
        if ($newState === 'efectivo') $msg = '💵 Efectivo';
        if ($newState === 'yape') $msg = '📱 Yape';
        
        echo json_encode(['success' => true, 'estado_pago' => $newState, 'message' => $msg]);
        break;

    // ─── ESTADÍSTICAS ───────────────────────────────────────
    case 'get_stats':
        $total = $pdo->query("SELECT COUNT(*) FROM entrega_cliente")->fetchColumn();
        $entregados = $pdo->query("SELECT COUNT(*) FROM entrega_cliente WHERE entregado = 1")->fetchColumn();
        $conArroz = $pdo->query("SELECT COUNT(*) FROM entrega_cliente WHERE con_arroz = 1")->fetchColumn();
        $pagados = $pdo->query("SELECT COUNT(*) FROM venta WHERE estado_pago = 'pagado'")->fetchColumn();
        $montoTotal = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta")->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => $total,
                'entregados' => $entregados,
                'pendientes' => $total - $entregados,
                'con_arroz' => $conArroz,
                'pagados' => $pagados,
                'monto_total' => $montoTotal
            ]
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
