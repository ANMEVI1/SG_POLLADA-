<?php
ob_start();
// Generador de Reporte PDF General de Cuadre de Caja
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/fpdf.php';

// Check ID
$cuadre_id = $_GET['cuadre_id'] ?? null;
if (!$cuadre_id) {
    die("ID de cuadre no proporcionado.");
}

// Fetch Cuadre
$stmt = $pdo->prepare("SELECT * FROM cuadre_caja WHERE id = ?");
$stmt->execute([$cuadre_id]);
$cuadre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cuadre) {
    die("Cuadre no encontrado.");
}

// Initialize FPDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

// Título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE GENERAL DE CUADRE #' . $cuadre['id'], 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Apertura: ' . $cuadre['hora_apertura'] . ' | Cierre: ' . ($cuadre['hora_cierre'] ?: 'ABIERTO'), 0, 1, 'C');
$pdf->Ln(5);

// ==========================================
// RESUMEN FINANCIERO
// ==========================================
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 8, ' 1. RESUMEN FINANCIERO', 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);
// Left Column
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->Cell(60, 8, ' Total Ventas (Ingresos):', 1, 0);
$pdf->Cell(35, 8, 'S/ ' . number_format((float)$cuadre['monto_total_ventas'], 2), 1, 1, 'R');

$pdf->SetXY($x, $y + 8);
$pdf->Cell(60, 8, ' Cobrado en Efectivo:', 1, 0);
$pdf->Cell(35, 8, 'S/ ' . number_format((float)$cuadre['monto_efectivo'], 2), 1, 1, 'R');

$pdf->SetXY($x, $y + 16);
$pdf->Cell(60, 8, ' Cobrado en Yape/Plin:', 1, 0);
$pdf->Cell(35, 8, 'S/ ' . number_format((float)$cuadre['monto_yape'], 2), 1, 1, 'R');

$pdf->SetXY($x, $y + 24);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 8, ' Egresos (Gastos Imprevistos):', 1, 0);
$pdf->Cell(35, 8, 'S/ ' . number_format((float)$cuadre['gasto_total'], 2), 1, 1, 'R');

// Right Column (posicionando)
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY($x + 95, $y);
$pdf->Cell(60, 8, ' Ganancia Neta Teorica:', 1, 0);
$pdf->Cell(35, 8, 'S/ ' . number_format((float)$cuadre['ganancia_neta'], 2), 1, 1, 'R');

if ($cuadre['efectivo_contado'] !== null) {
    $pdf->SetXY($x + 95, $y + 8);
    $pdf->Cell(60, 8, ' Efectivo Real Contado:', 1, 0);
    $pdf->Cell(35, 8, 'S/ ' . number_format((float)$cuadre['efectivo_contado'], 2), 1, 1, 'R');
    
    $pdf->SetXY($x + 95, $y + 16);
    $descStr = $cuadre['descuadre'] < 0 ? ' FALTANTE EN CAJA:' : ($cuadre['descuadre'] > 0 ? ' SOBRANTE EN CAJA:' : ' DESCUADRE:');
    if ($cuadre['descuadre'] < 0) {
        $pdf->SetTextColor(200, 0, 0); // Rojo si falta
    }
    $pdf->Cell(60, 8, $descStr, 1, 0);
    $pdf->Cell(35, 8, 'S/ ' . number_format((float)$cuadre['descuadre'], 2), 1, 1, 'R');
    $pdf->SetTextColor(0, 0, 0); // Reset
}

$pdf->SetXY($x + 95, $y + 24);
$pdf->Cell(60, 8, ' Entregas Totales:', 1, 0);
$pdf->Cell(35, 8, $cuadre['total_entregas'] ?? 0, 1, 1, 'R');

$pdf->SetXY($x + 95, $y + 32);
$pdf->Cell(60, 8, ' Pidieron Con Arroz:', 1, 0);
$pdf->Cell(35, 8, $cuadre['total_con_arroz'] ?? 0, 1, 1, 'R');

// Para asegurar que la siguiente sección esté debajo de ambas columnas
$pdf->SetY(max($pdf->GetY(), $y + 45));
$pdf->Ln(5);
// ==========================================
// LISTADO GENERAL DE CLIENTES
// ==========================================
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, ' 2. ESTADO GENERAL DE TODOS LOS CLIENTES', 1, 1, 'L', true);

// Header Tabla
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(15, 8, 'COD', 1, 0, 'C');
$pdf->Cell(55, 8, 'NOMBRE', 1, 0, 'C');
$pdf->Cell(45, 8, 'ZONA', 1, 0, 'C');
$pdf->Cell(25, 8, 'ESTADO', 1, 0, 'C');
$pdf->Cell(30, 8, 'PAGO', 1, 0, 'C');
$pdf->Cell(20, 8, 'MONTO', 1, 1, 'C');

// Fetch All Clients (Isolating data only for this cuadre_id)
$query = "
    SELECT 
        c.codigo_4digitos as cod,
        c.nombre,
        c.direccion,
        z.nombre as zona,
        v.monto,
        v.estado_pago,
        ec.entregado
    FROM cliente c
    LEFT JOIN zona_entrega z ON c.zona_entrega_id = z.id
    LEFT JOIN entrega_cliente ec ON ec.cliente_id = c.id 
        AND ec.created_at >= (SELECT hora_apertura FROM cuadre_caja WHERE id = :cuadre_id)
        AND ec.created_at <= (SELECT COALESCE(hora_cierre, NOW()) FROM cuadre_caja WHERE id = :cuadre_id2)
    LEFT JOIN venta v ON v.entrega_cliente_id = ec.id
    WHERE 
        (ec.id IS NOT NULL) OR 
        (c.created_at >= (SELECT hora_apertura FROM cuadre_caja WHERE id = :cuadre_id3)
         AND c.created_at <= (SELECT COALESCE(hora_cierre, NOW()) FROM cuadre_caja WHERE id = :cuadre_id4))
    ORDER BY c.codigo_4digitos ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'cuadre_id' => $cuadre_id, 
    'cuadre_id2' => $cuadre_id,
    'cuadre_id3' => $cuadre_id,
    'cuadre_id4' => $cuadre_id
]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf->SetFont('Arial', '', 8);
foreach ($clientes as $c) {
    // Sanitizar texto para FPDF (que no soporta UTF-8 directo en su version default)
    $nombre = mb_convert_encoding(mb_substr($c['nombre'], 0, 30, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
    $zona = mb_convert_encoding(mb_substr($c['zona'] ?? $c['direccion'] ?? 'Sin zona', 0, 25, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
    
    $estado = 'NO ENTREGADO';
    $pago = '-';
    $monto = '-';
    
    if ($c['entregado'] == 1) {
        if ($c['cuadre_caja_id'] == $cuadre_id) {
            $estado = 'ENTREGADO';
            $pago = strtoupper($c['estado_pago']);
            $monto = 'S/ ' . number_format((float)$c['monto'], 2);
        } else {
            $estado = 'OTRO TURNO';
            $pago = 'OMITIDO';
        }
    }
    
    $pdf->Cell(15, 7, $c['cod'], 1, 0, 'C');
    $pdf->Cell(55, 7, $nombre, 1, 0, 'L');
    $pdf->Cell(45, 7, $zona, 1, 0, 'L');
    
    // Colores dependiendo del estado
    if ($estado === 'ENTREGADO') {
        $pdf->SetTextColor(0, 100, 0); // Verde
    } else if ($estado === 'NO ENTREGADO') {
        $pdf->SetTextColor(200, 0, 0); // Rojo
    } else {
        $pdf->SetTextColor(100, 100, 100); // Gris
    }
    
    $pdf->Cell(25, 7, $estado, 1, 0, 'C');
    $pdf->SetTextColor(0, 0, 0); // Reset
    
    $pdf->Cell(30, 7, $pago, 1, 0, 'C');
    $pdf->Cell(20, 7, $monto, 1, 1, 'R');
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(0, 8, ' 3. DETALLE DE GASTOS E INVERSIONES', 1, 1, 'L', true);

// Header Gastos
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(25, 8, 'TIPO', 1, 0, 'C');
$pdf->Cell(125, 8, 'DESCRIPCION / NOMBRE', 1, 0, 'C');
$pdf->Cell(40, 8, 'MONTO', 1, 1, 'C');

$gastosQuery = "
    SELECT 'PRODUCTO' as tipo, nombre as descripcion, subtotal as monto 
    FROM producto 
    WHERE comprado = 1 AND cuadre_caja_id = :cid
    UNION ALL
    SELECT 'IMPREVISTO' as tipo, descripcion, monto 
    FROM egreso_imprevisto 
    WHERE cuadre_caja_id = :cid2
    ORDER BY tipo, descripcion
";
$stmtGastos = $pdo->prepare($gastosQuery);
$stmtGastos->execute(['cid' => $cuadre_id, 'cid2' => $cuadre_id]);
$gastos = $stmtGastos->fetchAll(PDO::FETCH_ASSOC);

$pdf->SetFont('Arial', '', 8);
$totalG = 0;
if (count($gastos) > 0) {
    foreach ($gastos as $g) {
        $desc = mb_convert_encoding(mb_substr($g['descripcion'], 0, 70, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
        
        $pdf->Cell(25, 7, $g['tipo'], 1, 0, 'C');
        $pdf->Cell(125, 7, ' ' . $desc, 1, 0, 'L');
        $pdf->Cell(40, 7, 'S/ ' . number_format((float)$g['monto'], 2), 1, 1, 'R');
        $totalG += (float)$g['monto'];
    }
    // Fila total
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(150, 7, 'TOTAL GASTOS:', 1, 0, 'R');
    $pdf->Cell(40, 7, 'S/ ' . number_format($totalG, 2), 1, 1, 'R');
} else {
    $pdf->Cell(190, 8, 'NO HAY GASTOS REGISTRADOS EN ESTA JORNADA', 1, 1, 'C');
}

// Limpiar cualquier basura u output accidental antes de enviar el PDF
if (ob_get_length()) ob_end_clean();

// Output PDF inline
$pdf->Output('I', 'Reporte_Cuadre_' . $cuadre['id'] . '.pdf');
?>
