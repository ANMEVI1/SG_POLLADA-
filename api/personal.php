<?php
/**
 * API - Módulo de Personal y Dividendos
 * Acciones: update_reconocimiento, get_dividendos
 */

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'update_reconocimiento':
        $stmt = $pdo->prepare("UPDATE personal SET reconocimiento_monetario = ? WHERE id = ?");
        $stmt->execute([floatval($_POST['monto']), $_POST['id']]);
        echo json_encode(['success' => true, 'message' => 'Reconocimiento actualizado']);
        break;

    case 'get_dividendos':
        // Total ventas
        $totalVentas = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta")->fetchColumn();
        
        // Total gastos (productos comprados + egresos)
        $totalProd = $pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM producto WHERE comprado = 1")->fetchColumn();
        $totalEgr = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto")->fetchColumn();
        $gastoTotal = round($totalProd + $totalEgr, 2);
        
        // Total reconocimientos
        $totalRecon = $pdo->query("SELECT COALESCE(SUM(reconocimiento_monetario), 0) FROM personal")->fetchColumn();
        
        // Ganancia neta (después de gastos y reconocimientos)
        $gananciaNeta = round($totalVentas - $gastoTotal - $totalRecon, 2);
        
        // Inversionistas
        $inversionistas = $pdo->query("SELECT COUNT(*) FROM personal WHERE participacion = 'inversionista'")->fetchColumn();
        $porPersona = $inversionistas > 0 ? round($gananciaNeta / $inversionistas, 2) : 0;
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total_ventas' => $totalVentas,
                'gasto_total' => $gastoTotal,
                'total_reconocimientos' => $totalRecon,
                'ganancia_neta' => $gananciaNeta,
                'inversionistas' => $inversionistas,
                'por_persona' => $porPersona
            ]
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
