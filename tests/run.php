<?php
/**
 * Test Runner - SG_POLLADA
 * Ejecuta todos los archivos de prueba en el directorio `tests/`
 */

if (php_sapi_name() !== 'cli') {
    die("Este script de pruebas solo puede ejecutarse desde la terminal (consola).");
}

echo "\n🚀 INICIANDO SUITE DE PRUEBAS - SG_POLLADA\n";
echo "=================================================\n\n";

$testFiles = glob(__DIR__ . '/test_*.php');
$testFiles = array_filter($testFiles, function($file) {
    return basename($file) !== 'test_helper.php';
});

if (empty($testFiles)) {
    echo "No se encontraron archivos de prueba.\n";
    exit;
}

foreach ($testFiles as $file) {
    echo ">> Cargando archivo: " . basename($file) . "\n";
    require_once $file;
}

echo "\n✨ SUITE DE PRUEBAS COMPLETADA.\n\n";
