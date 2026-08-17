<?php
/**
 * API - Módulo de Cuadre de Caja
 * Acciones: abrir_caja, cerrar_caja, get_historial
 */

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'abrir_caja':
        $pinInput = $_POST['pin'] ?? '';
        $pinReal = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'pin_cierre'")->fetchColumn();
        if ($pinInput !== $pinReal) {
            echo json_encode(['success' => false, 'message' => 'PIN incorrecto']);
            exit;
        }
        $open = $pdo->query("SELECT id FROM cuadre_caja WHERE estado = 'abierto' LIMIT 1")->fetch();
        if ($open) {
            echo json_encode(['success' => false, 'message' => 'Ya hay una caja abierta']);
            break;
        }
        $pdo->query("INSERT INTO cuadre_caja (hora_apertura, estado) VALUES (NOW(), 'abierto')");
        echo json_encode(['success' => true, 'message' => '🟢 Jornada abierta']);
        break;

    case 'cerrar_caja':
    case 'cerrar_caja_hard':
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
        
        $efectivoFisico = isset($_POST['efectivo_fisico']) ? floatval($_POST['efectivo_fisico']) : 0.0;
        
        // Aislar jornada (esto vincula los registros sueltos al cuadre actual)
        $pdo->query("UPDATE venta SET cuadre_caja_id = {$open['id']} WHERE cuadre_caja_id IS NULL");
        $pdo->query("UPDATE egreso_imprevisto SET cuadre_caja_id = {$open['id']} WHERE cuadre_caja_id IS NULL");
        $pdo->query("UPDATE producto SET cuadre_caja_id = {$open['id']} WHERE comprado = 1 AND cuadre_caja_id IS NULL");
        
        // Calculate totals for THIS shift
        $totalVentas = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE cuadre_caja_id = {$open['id']}")->fetchColumn();
        $totalEfectivo = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE estado_pago = 'efectivo' AND cuadre_caja_id = {$open['id']}")->fetchColumn();
        $totalYape = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE estado_pago = 'yape' AND cuadre_caja_id = {$open['id']}")->fetchColumn();
        
        $totalProd = $pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM producto WHERE cuadre_caja_id = {$open['id']}")->fetchColumn();
        $totalEgr = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto WHERE cuadre_caja_id = {$open['id']}")->fetchColumn();
        $egresosDeCaja = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto WHERE salio_de_caja = 1 AND cuadre_caja_id = {$open['id']}")->fetchColumn();
        
        $gastoTotal = round($totalProd + $totalEgr, 2);
        $gananciaNeta = round($totalVentas - $gastoTotal, 2);
        
        $efectivoTeorico = round($totalEfectivo - $egresosDeCaja, 2);
        $descuadre = round($efectivoFisico - $efectivoTeorico, 2);
        
        $totalEntregas = $pdo->query("SELECT COUNT(*) FROM venta WHERE cuadre_caja_id = {$open['id']}")->fetchColumn();
        $totalArroz = $pdo->query("SELECT COUNT(*) FROM entrega_cliente ec JOIN venta v ON v.entrega_cliente_id = ec.id WHERE ec.con_arroz = 1 AND v.cuadre_caja_id = {$open['id']}")->fetchColumn();
        $totalPagados = $pdo->query("SELECT COUNT(*) FROM venta WHERE estado_pago != 'pendiente' AND cuadre_caja_id = {$open['id']}")->fetchColumn();
        $totalPendientes = $pdo->query("SELECT COUNT(*) FROM venta WHERE estado_pago = 'pendiente' AND cuadre_caja_id = {$open['id']}")->fetchColumn();
        
        // Update cuadre
        $stmt = $pdo->prepare("
            UPDATE cuadre_caja SET 
                hora_cierre = NOW(),
                monto_total_ventas = ?,
                gasto_total = ?,
                ganancia_neta = ?,
                monto_efectivo = ?,
                monto_yape = ?,
                efectivo_contado = ?,
                descuadre = ?,
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
            $totalEfectivo,
            $totalYape,
            $efectivoFisico,
            $descuadre,
            $totalEntregas,
            $totalArroz,
            $totalPagados,
            $totalPendientes,
            $open['id']
        ]);
        
        // Ejecutar borrado lógico según el tipo de cierre
        if ($action === 'cerrar_caja_hard') {
            // Archivar absolutamente todo
            $pdo->query("UPDATE entrega_cliente SET archivado = 1 WHERE archivado = 0");
            $pdo->query("UPDATE cliente SET archivado = 1 WHERE archivado = 0");
            $pdo->query("UPDATE producto SET archivado = 1 WHERE archivado = 0");
            $pdo->query("UPDATE egreso_imprevisto SET archivado = 1 WHERE archivado = 0");
            $msg = '🔴 Jornada Cerrada y Datos Limpiados (Hard Reset)';
        } else {
            // Cierre normal: solo limpia la interfaz de entregas
            $pdo->query("UPDATE entrega_cliente SET archivado = 1 WHERE archivado = 0");
            $msg = '🔴 Jornada Cerrada (Clientes intactos)';
        }
        
        echo json_encode(['success' => true, 'message' => $msg]);
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
