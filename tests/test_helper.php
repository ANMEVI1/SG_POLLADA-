<?php
/**
 * Test Helper - SG_POLLADA
 * Envolver pruebas en transacciones de Base de Datos para asegurar Rollback.
 */

require_once __DIR__ . '/../config/database.php';

// Utilidad para colores en consola
function p_success($msg) { echo "\033[32m[PASS] $msg\033[0m\n"; }
function p_error($msg) { echo "\033[31m[FAIL] $msg\033[0m\n"; }
function p_info($msg) { echo "\033[36m[INFO] $msg\033[0m\n"; }

function run_test($name, $callback) {
    global $pdo;
    
    echo "====================================\n";
    echo "Ejecutando Test: $name\n";
    echo "------------------------------------\n";
    
    // Iniciar transacción: todo lo que pase aquí no afectará la base de datos real
    $pdo->beginTransaction();
    
    try {
        // Ejecutar prueba
        $callback($pdo);
        p_success("Prueba finalizada sin excepciones.");
    } catch (Exception $e) {
        p_error("Excepción lanzada: " . $e->getMessage());
    } finally {
        // ROLLBACK OBLIGATORIO: Deshacer todos los cambios en la BD
        $pdo->rollBack();
        p_info("Rollback ejecutado exitosamente. Base de datos intacta.\n");
    }
}

// Pequeña función de aserción (Assert)
function assert_equals($expected, $actual, $message = '') {
    if ($expected === $actual) {
        p_success("Assert OK: " . ($message ?: "Valor esperado obtenido."));
    } else {
        throw new Exception("Assert Fail: Se esperaba '$expected', pero se obtuvo '$actual'. " . $message);
    }
}
