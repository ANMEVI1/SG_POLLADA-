<?php
require 'config/database.php';

// Add fecha_evento to configuracion if not exists
$stmt = $pdo->prepare("INSERT IGNORE INTO configuracion (clave, valor) VALUES ('fecha_evento', 'Domingo 24 de Agosto - 12:00 PM a 4:00 PM')");
$stmt->execute();

echo "Configuracion actualizada.\n";
