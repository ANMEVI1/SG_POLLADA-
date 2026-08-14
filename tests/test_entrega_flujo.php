<?php
/**
 * Test: Flujo de Entregas y Pagos
 */

require_once __DIR__ . '/test_helper.php';

run_test('Validar ciclo de pago: Pendiente -> Efectivo -> Yape', function($pdo) {
    
    // 1. Crear un cliente ficticio (dentro de la transacción)
    $stmt = $pdo->prepare("INSERT INTO cliente (codigo_4digitos, nombre, direccion) VALUES (?, ?, ?)");
    $stmt->execute(['9999', 'Cliente de Prueba', 'Calle Falsa 123']);
    $clienteId = $pdo->lastInsertId();
    assert_equals(true, $clienteId > 0, "Cliente creado correctamente en BD Test.");

    // Insertar un platillo si no hay
    $platilloId = $pdo->query("SELECT id FROM platillo LIMIT 1")->fetchColumn();
    if (!$platilloId) {
        $pdo->query("INSERT INTO platillo (nombre, precio) VALUES ('Pollada', 15.00)");
        $platilloId = $pdo->lastInsertId();
    }

    // Insertar entrega_cliente
    $stmt = $pdo->prepare("INSERT INTO entrega_cliente (cliente_id, platillo_id, entregado) VALUES (?, ?, 0)");
    $stmt->execute([$clienteId, $platilloId]);
    $entregaId = $pdo->lastInsertId();

    // 2. Simular que se le marca como Entregado
    $stmt = $pdo->prepare("INSERT INTO venta (entrega_cliente_id, cliente_id, monto, estado_pago) VALUES (?, ?, ?, 'pendiente')");
    $stmt->execute([$entregaId, $clienteId, 20.00]);
    $ventaId = $pdo->lastInsertId();
    
    $venta = $pdo->query("SELECT * FROM venta WHERE id = $ventaId")->fetch();
    assert_equals('pendiente', $venta['estado_pago'], "Estado inicial de la venta debe ser pendiente.");

    // 3. Simular ciclo 1: Cambiar a Efectivo
    $stmt = $pdo->prepare("UPDATE venta SET estado_pago = 'efectivo' WHERE id = ?");
    $stmt->execute([$ventaId]);
    $ventaEfectivo = $pdo->query("SELECT * FROM venta WHERE id = $ventaId")->fetch();
    assert_equals('efectivo', $ventaEfectivo['estado_pago'], "Estado cambiado a efectivo exitosamente.");

    // 4. Simular ciclo 2: Cambiar a Yape
    $stmt = $pdo->prepare("UPDATE venta SET estado_pago = 'yape' WHERE id = ?");
    $stmt->execute([$ventaId]);
    $ventaYape = $pdo->query("SELECT * FROM venta WHERE id = $ventaId")->fetch();
    assert_equals('yape', $ventaYape['estado_pago'], "Estado cambiado a yape exitosamente.");
    
    // Al finalizar este scope, test_helper.php hará automáticamente $pdo->rollBack() 
    // y el cliente_id '9999' desaparecerá de la base de datos para siempre.
});
