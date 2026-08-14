<?php
/**
 * Test automatizado para verificar la estructura HTML (DOM) y clases CSS
 * Este script escanea los archivos en views/ para detectar <div>, <span>, 
 * y otras etiquetas mal cerradas que rompan el diseño (Flexbox/Grid).
 */

echo "Iniciando escaneo de UI Structure...\n";
echo str_repeat("-", 40) . "\n";

$viewsDir = __DIR__ . '/../views/';
if (!is_dir($viewsDir)) {
    die("Directorio views/ no encontrado.\n");
}

$files = glob($viewsDir . '*.php');
$hasErrors = false;

// Ignorar advertencias internas de libxml (tags inválidos, etc.)
libxml_use_internal_errors(true);

foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // Extraer y purgar etiquetas PHP para evaluar solo el esqueleto HTML
    // Se elimina el contenido de PHP pero se mantienen las líneas base para mejor referencia
    $html = preg_replace('/<\?php.*?\?>|<\?=.*?\?>/s', '', $content);
    
    // Envolver en <html><body> para evitar errores de multiples raices
    $html = '<html><body>' . $html . '</body></html>';
    
    $doc = new DOMDocument();
    // Suprimir warnings
    @$doc->loadHTML($html);
    
    $errors = libxml_get_errors();
    $fileErrors = [];
    
    foreach ($errors as $error) {
        // Códigos irrelevantes para estructura básica:
        if ($error->code == 801) continue; // Tag mismatch es 76. 801 es invalid tag name
        if ($error->code == 800) continue; // Empty tag name
        if ($error->code == 73)  continue; // htmlParseEntityRef: no name
        if ($error->code == 513) continue; // ID already defined (puede pasar en plantillas)
        
        if (strpos($error->message, 'Unexpected end tag') !== false || 
            strpos($error->message, 'Opening and ending tag mismatch') !== false) {
            $fileErrors[] = "Línea " . $error->line . ": " . trim($error->message);
        }
    }
    
    libxml_clear_errors();
    
    if (count($fileErrors) > 0) {
        $hasErrors = true;
        echo "❌ [ERROR] Estructura rota en: " . $filename . "\n";
        foreach ($fileErrors as $err) {
            echo "   -> " . $err . "\n";
        }
    } else {
        echo "✅ [OK] Estructura limpia en: " . $filename . "\n";
    }
}

echo str_repeat("-", 40) . "\n";
if ($hasErrors) {
    echo "🚨 TEST FALLIDO: Se detectaron etiquetas HTML sin cerrar o mal emparejadas.\n";
    exit(1);
} else {
    echo "🎉 TEST PASADO: Todas las vistas tienen una estructura DOM balanceada.\n";
    exit(0);
}
