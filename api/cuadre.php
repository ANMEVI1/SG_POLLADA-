<?php
/**
 * API - Módulo de Cuadre de Caja
 * Acciones: abrir_caja, cerrar_caja, get_historial
 */

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'abrir_caja':
        // Check if there's already an open cuadre
        $open = $pdo->query("SELECT id FROM cuadre_caja WHERE estado = 'abierto' LIMIT 1")->fetch();
        if ($open) {
            echo json_encode(['success' => false, 'message' => 'Ya hay una caja abierta']);
            break;
        }
        $stmt = $pdo->prepare("INSERT INTO cuadre_caja (hora_apertura, estado) VALUES (NOW(), 'abierto')");
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => '🟢 Jornada abierta']);
        break;

    case 'cerrar_caja':
        $pin = $_POST['pin'] ?? '';
        
        // Verify PIN
        $pinDB = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'pin_cierre'")->fetchColumn();
        if ($pin !== $pinDB) {
            echo json_encode(['success' => false, 'message' => 'PIN incorrecto']);
            break;
        }
        
        // Get open cuadre
        $open = $pdo->query("SELECT id FROM cuadre_caja WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1")->fetch();
        if (!$open) {
            echo json_encode(['success' => false, 'message' => 'No hay caja abierta']);
            break;
        }
        
        // Calculate totals
        $totalVentas = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta")->fetchColumn();
        $totalProd = $pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM producto WHERE comprado = 1")->fetchColumn();
        $totalEgr = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto")->fetchColumn();
        $gastoTotal = round($totalProd + $totalEgr, 2);
        $gananciaNeta = round($totalVentas - $gastoTotal, 2);
        
        $totalEntregas = $pdo->query("SELECT COUNT(*) FROM entrega_cliente WHERE entregado = 1")->fetchColumn();
        $totalArroz = $pdo->query("SELECT COUNT(*) FROM entrega_cliente WHERE con_arroz = 1 AND entregado = 1")->fetchColumn();
        $totalPagados = $pdo->query("SELECT COUNT(*) FROM venta WHERE estado_pago = 'pagado'")->fetchColumn();
        $totalPendientes = $pdo->query("SELECT COUNT(*) FROM venta WHERE estado_pago = 'pendiente'")->fetchColumn();
        
        // Update cuadre
        $stmt = $pdo->prepare("
            UPDATE cuadre_caja SET 
                hora_cierre = NOW(),
                monto_total_ventas = ?,
                gasto_total = ?,
                ganancia_neta = ?,
                estado = 'cerrado',
                total_entregas = ?,
                total_con_arroz = ?,
                total_pagados = ?,
                total_pendientes = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $totalVentas,
            $gastoTotal,
            $gananciaNeta,
            $totalEntregas,
            $totalArroz,
            $totalPagados,
            $totalPendientes,
            $open['id']
        ]);
        
        echo json_encode(['success' => true, 'message' => '🔴 Jornada cerrada exitosamente']);
        break;

    case 'update_pin':
        $pinActual = $_POST['pin_actual'] ?? '';
        $pinNuevo = $_POST['pin_nuevo'] ?? '';
        
        $pinDB = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'pin_cierre'")->fetchColumn();
        if ($pinActual !== $pinDB) {
            echo json_encode(['success' => false, 'message' => 'PIN actual incorrecto']);
            break;
        }
        if (strlen($pinNuevo) !== 4 || !ctype_digit($pinNuevo)) {
            echo json_encode(['success' => false, 'message' => 'El nuevo PIN debe ser de 4 dígitos']);
            break;
        }
        $pdo->prepare("UPDATE configuracion SET valor = ? WHERE clave = 'pin_cierre'")->execute([$pinNuevo]);
        echo json_encode(['success' => true, 'message' => 'PIN actualizado']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
