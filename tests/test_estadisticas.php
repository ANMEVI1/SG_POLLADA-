<?php
/**
 * Test: Estadísticas de Entregas y Cuadres
 */

require_once __DIR__ . '/test_helper.php';

run_test('Validar conteo de Pagados y Deudores', function($pdo) {
    // Asegurar que hay un platillo activo
    $platilloId = $pdo->query("SELECT id FROM platillo LIMIT 1")->fetchColumn();
    if (!$platilloId) {
        $pdo->query("INSERT INTO platillo (nombre, precio) VALUES ('Pollada QA', 15.00)");
        $platilloId = $pdo->lastInsertId();
    }

    // 1. Cliente 1: Solo registrado (No entregado, No pagado) - Falso positivo reportado
    $stmt = $pdo->prepare("INSERT INTO cliente (codigo_4digitos, nombre, direccion) VALUES (?, ?, ?)");
    $stmt->execute(['9901', 'Cliente Solo Registrado', 'Dir 1']);
    $c1 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO entrega_cliente (cliente_id, platillo_id, entregado) VALUES (?, ?, 0)");
    $stmt->execute([$c1, $platilloId]);

    // 2. Cliente 2: Entregado pero NO pagado (Deudor)
    $stmtCliente = $pdo->prepare("INSERT INTO cliente (codigo_4digitos, nombre, direccion) VALUES (?, ?, ?)");
    $stmtCliente->execute(['9902', 'Cliente Deudor', 'Dir 2']);
    $c2 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO entrega_cliente (cliente_id, platillo_id, entregado) VALUES (?, ?, 1)");
    $stmt->execute([$c2, $platilloId]);
    $e2 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO venta (entrega_cliente_id, cliente_id, monto, estado_pago) VALUES (?, ?, 15.00, 'pendiente')");
    $stmt->execute([$e2, $c2]);

    // 3. Cliente 3: Entregado y Pagado Efectivo
    $stmtCliente->execute(['9903', 'Cliente Efectivo', 'Dir 3']);
    $c3 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO entrega_cliente (cliente_id, platillo_id, entregado) VALUES (?, ?, 1)");
    $stmt->execute([$c3, $platilloId]);
    $e3 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("INSERT INTO venta (entrega_cliente_id, cliente_id, monto, estado_pago) VALUES (?, ?, 15.00, 'efectivo')");
    $stmt->execute([$e3, $c3]);


    // -- Ejecutar la misma lógica de views/entrega.php --
    $entregas = $pdo->query("
        SELECT ec.*, v.estado_pago
        FROM entrega_cliente ec
        JOIN cliente c ON ec.cliente_id = c.id
        LEFT JOIN venta v ON v.entrega_cliente_id = ec.id
        WHERE c.codigo_4digitos IN ('9901', '9902', '9903')
    ")->fetchAll();

    // Stats
    $totalEntregados = count(array_filter($entregas, fn($e) => $e['entregado']));
    $totalPagados = count(array_filter($entregas, fn($e) => $e['estado_pago'] !== null && $e['estado_pago'] !== 'pendiente'));
    $totalDeudores = count(array_filter($entregas, fn($e) => $e['entregado'] && $e['estado_pago'] === 'pendiente'));

    assert_equals(2, $totalEntregados, "Deberían haber 2 clientes entregados");
    assert_equals(1, $totalPagados, "Debería haber 1 cliente pagado (Efectivo)");
    assert_equals(1, $totalDeudores, "Debería haber 1 cliente deudor (Pendiente)");
});
