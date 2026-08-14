<?php
$css = file_get_contents(__DIR__ . '/../assets/css/styles.css');

$openCount = substr_count($css, '{');
$closeCount = substr_count($css, '}');

echo "Verificando CSS (styles.css)...\n";
echo "Llaves de apertura '{' : $openCount\n";
echo "Llaves de cierre '}' : $closeCount\n";

if ($openCount === $closeCount) {
    echo "✅ [OK] CSS estructuralmente balanceado.\n";
    exit(0);
} else {
    echo "❌ [ERROR] CSS roto. Llaves desbalanceadas.\n";
    exit(1);
}
