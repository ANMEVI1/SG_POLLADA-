<?php
require_once '../config/database.php';
require_once '../lib/fpdf.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    die("ID de cliente inválido.");
}

// Fetch cliente data
$stmt = $pdo->prepare("
    SELECT c.*, z.nombre AS zona_nombre 
    FROM cliente c
    LEFT JOIN zona_entrega z ON c.zona_entrega_id = z.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Cliente no encontrado.");
}

// Fetch config (optional)
$titulo = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'titulo_evento'")->fetchColumn() ?: 'GRAN POLLADA BAILABLE';
$precio = $pdo->query("SELECT precio FROM platillo WHERE estado = 'activo' LIMIT 1")->fetchColumn() ?: '15.00';

// Initialize PDF with Ticket size (80mm width, 140mm height)
$pdf = new FPDF('P', 'mm', array(80, 140));
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Sanitization for FPDF
$titulo = mb_convert_encoding(mb_substr($titulo, 0, 30, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
$nombre = mb_convert_encoding(mb_substr($cliente['nombre'], 0, 35, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
$zona = mb_convert_encoding(mb_substr($cliente['zona_nombre'] ?? 'Sin zona', 0, 25, 'UTF-8'), 'ISO-8859-1', 'UTF-8');
$direccion = mb_convert_encoding(mb_substr($cliente['direccion'], 0, 45, 'UTF-8'), 'ISO-8859-1', 'UTF-8');

// Title
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(70, 8, $titulo, 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(70, 5, '-------------------------------------------------------', 0, 1, 'C');
$pdf->Ln(5);

// TICKET NUMBER
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 6, 'TICKET Nro:', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 32);
$pdf->SetTextColor(230, 81, 0); // Orange color
$pdf->Cell(70, 15, $cliente['codigo_4digitos'], 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0); // Reset
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 9);
$pdf->Cell(70, 5, '-------------------------------------------------------', 0, 1, 'C');
$pdf->Ln(3);

// CLIENT DETAILS
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(70, 6, 'CLIENTE:', 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->MultiCell(70, 6, $nombre, 0, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(70, 5, 'ZONA: ' . $zona, 0, 1, 'L');
$pdf->Cell(70, 5, 'DIR: ' . $direccion, 0, 1, 'L');
$pdf->Ln(5);

// PRICE
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(70, 8, 'PRECIO: S/ ' . number_format((float)$precio, 2), 1, 1, 'C', false);
$pdf->Ln(5);

// FOOTER MESSAGE
$pdf->SetFont('Arial', 'I', 8);
$pdf->MultiCell(70, 4, mb_convert_encoding("Presenta este ticket digital al momento de recibir tu pedido.\n¡Gracias por tu apoyo!", 'ISO-8859-1', 'UTF-8'), 0, 'C');

// Generate Output (Force Download 'D' for WhatsApp sharing flow)
$filename = 'Ticket_' . $cliente['codigo_4digitos'] . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $cliente['nombre'])) . '.pdf';

if (ob_get_length()) ob_end_clean();
// We use 'I' for local preview/development, but 'D' for forcing download on mobile
// However, the user said "al hacer clic en el link se descargue", 'D' forces download on most browsers
$pdf->Output('D', $filename);
?>
