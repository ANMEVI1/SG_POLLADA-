<?php
/**
 * Vista: Módulo de Entrega y Ventas (Panel Operacional Tiempo Real)
 * Tabs: Entregas | Clientes
 */

// ─── DATA LOADING ───────────────────────────────────────────
$zonas = $pdo->query("SELECT * FROM zona_entrega ORDER BY id")->fetchAll();
$platillos = $pdo->query("SELECT * FROM platillo WHERE estado = 'activo'")->fetchAll();

// Clientes
$clientes = $pdo->query("
    SELECT c.*, z.nombre AS zona_nombre 
    FROM cliente c 
    LEFT JOIN zona_entrega z ON c.zona_entrega_id = z.id 
    ORDER BY c.codigo_4digitos
")->fetchAll();

// Entregas con datos completos
$entregas = $pdo->query("
    SELECT ec.*, 
           c.nombre AS cliente_nombre, 
           c.direccion AS cliente_direccion,
           c.codigo_4digitos AS cliente_codigo,
           p.nombre AS platillo_nombre,
           p.precio AS platillo_precio,
           z.nombre AS zona_nombre,
           v.id AS venta_id,
           v.estado_pago
    FROM entrega_cliente ec
    JOIN cliente c ON ec.cliente_id = c.id
    JOIN platillo p ON ec.platillo_id = p.id
    LEFT JOIN zona_entrega z ON ec.zona_entrega_id = z.id
    LEFT JOIN venta v ON v.entrega_cliente_id = ec.id
    ORDER BY ec.entregado ASC, c.codigo_4digitos ASC
")->fetchAll();

// Stats
$totalEntregas = count($entregas);
$totalEntregados = count(array_filter($entregas, fn($e) => $e['entregado']));
$totalPendientes = $totalEntregas - $totalEntregados;
$totalArroz = count(array_filter($entregas, fn($e) => $e['con_arroz']));
$totalPagados = count(array_filter($entregas, fn($e) => $e['estado_pago'] === 'pagado'));
$montoTotal = array_sum(array_map(fn($e) => $e['entregado'] ? $e['platillo_precio'] : 0, $entregas));

// Next code
$inicio = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'codigo_cliente_inicio'")->fetchColumn() ?: '0347';
$maxCode = $pdo->query("SELECT MAX(CAST(codigo_4digitos AS UNSIGNED)) FROM cliente")->fetchColumn();
$nextCode = str_pad(max(intval($inicio), intval($maxCode) + 1), 4, '0', STR_PAD_LEFT);
?>

<!-- Tabs -->
<div class="section-tabs">
    <button class="tab-pill active" data-tab="entregas" data-group="ent">🍗 Entregas</button>
    <button class="tab-pill" data-tab="clientes" data-group="ent">👤 Clientes</button>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: ENTREGAS (PANEL OPERACIONAL) -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content active" id="tab-entregas" data-group="ent">

    <!-- Stats Row -->
    <div class="stats-row cols-3 mb-12">
        <div class="stat-card">
            <div class="stat-value" id="statTotal"><?= $totalEntregas ?></div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-value success" id="statEntregados"><?= $totalEntregados ?></div>
            <div class="stat-label">Entregados</div>
        </div>
        <div class="stat-card">
            <div class="stat-value danger" id="statPendientes"><?= $totalPendientes ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
    </div>
    <div class="stats-row cols-3 mb-12">
        <div class="stat-card">
            <div class="stat-value warning" id="statArroz"><?= $totalArroz ?></div>
            <div class="stat-label">Con Arroz</div>
        </div>
        <div class="stat-card">
            <div class="stat-value info" id="statPagados"><?= $totalPagados ?></div>
            <div class="stat-label">Pagados</div>
        </div>
        <div class="stat-card">
            <div class="stat-value success" id="statMonto">S/<?= number_format($montoTotal, 2) ?></div>
            <div class="stat-label">Vendido</div>
        </div>
    </div>

    <!-- Search -->
    <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Buscar por código, nombre o dirección..." 
               oninput="searchEntregas(this.value)" id="searchInput">
    </div>

    <!-- Filters -->
    <div class="filter-row">
        <button class="filter-pill active" data-filter="todos" onclick="filterEntregas('todos')">
            Todos <span class="filter-count"><?= $totalEntregas ?></span>
        </button>
        <button class="filter-pill" data-filter="pendientes" onclick="filterEntregas('pendientes')">
            Pendientes <span class="filter-count"><?= $totalPendientes ?></span>
        </button>
        <button class="filter-pill" data-filter="entregados" onclick="filterEntregas('entregados')">
            Entregados <span class="filter-count"><?= $totalEntregados ?></span>
        </button>
        <button class="filter-pill" data-filter="arroz" onclick="filterEntregas('arroz')">
            Con Arroz <span class="filter-count"><?= $totalArroz ?></span>
        </button>
        <button class="filter-pill" data-filter="pagados" onclick="filterEntregas('pagados')">
            Pagados <span class="filter-count"><?= $totalPagados ?></span>
        </button>
    </div>

    <!-- Delivery Cards -->
    <?php if (empty($entregas)): ?>
        <div class="empty-state">
            <div class="empty-icon">🍗</div>
            <p>No hay entregas asignadas. Registra clientes y genera las entregas.</p>
            <?php if (!empty($clientes)): ?>
                <button class="btn btn-primary mt-16" onclick="generarEntregas()">⚡ Generar Entregas</button>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div id="entregasList">
        <?php foreach ($entregas as $e): ?>
            <div class="delivery-card <?= $e['entregado'] ? 'entregado' : '' ?>" 
                 data-code="<?= $e['cliente_codigo'] ?>"
                 data-name="<?= htmlspecialchars($e['cliente_nombre']) ?>"
                 data-address="<?= htmlspecialchars($e['cliente_direccion']) ?>"
                 data-arroz="<?= $e['con_arroz'] ?>"
                 data-pagado="<?= $e['estado_pago'] === 'pagado' ? '1' : '0' ?>">
                
                <div class="dc-top">
                    <div class="dc-code"><?= $e['cliente_codigo'] ?></div>
                    <?php if ($e['zona_nombre']): ?>
                        <span class="dc-zone"><?= htmlspecialchars($e['zona_nombre']) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="dc-name"><?= htmlspecialchars($e['cliente_nombre']) ?></div>
                <div class="dc-address">📍 <?= htmlspecialchars($e['cliente_direccion']) ?></div>
                
                <div class="dc-toggles">
                    <div class="toggle-chip <?= $e['entregado'] ? 'active-success' : '' ?>" 
                         onclick="toggleEntregado(<?= $e['id'] ?>, this)">
                        <span class="chip-text"><?= $e['entregado'] ? 'Entregado ✓' : 'Entregar' ?></span>
                    </div>
                    <div class="toggle-chip <?= $e['con_arroz'] ? 'active-warning' : '' ?>" 
                         onclick="toggleArroz(<?= $e['id'] ?>, this)">
                        🍚 <span class="chip-text">Arroz</span>
                    </div>
                    <div class="toggle-chip <?= $e['estado_pago'] === 'pagado' ? 'active-info' : '' ?>" 
                         onclick="togglePago(<?= $e['id'] ?>, this)">
                        💵 <span class="chip-text"><?= $e['estado_pago'] === 'pagado' ? 'Pagado ✓' : 'Pagar' ?></span>
                    </div>
                </div>

                <?php if ($e['hora_entrega']): ?>
                    <div class="fs-sm text-muted mt-4">
                        Entregado: <?= date('h:i A', strtotime($e['hora_entrega'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Generate entregas button (when there are clientes without entregas) -->
    <?php 
    $sinEntrega = $pdo->query("SELECT COUNT(*) FROM cliente c LEFT JOIN entrega_cliente ec ON c.id = ec.cliente_id WHERE ec.id IS NULL")->fetchColumn();
    if ($sinEntrega > 0):
    ?>
        <button class="fab" onclick="generarEntregas()" title="Generar entregas (<?= $sinEntrega ?> clientes sin asignar)">⚡</button>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: CLIENTES -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content" id="tab-clientes" data-group="ent">
    <div class="d-flex justify-between align-center mb-12">
        <span class="fs-sm text-muted"><?= count($clientes) ?> clientes registrados</span>
        <button class="btn btn-primary btn-sm" onclick="newCliente()">+ Nuevo Cliente</button>
    </div>

    <?php if (empty($clientes)): ?>
        <div class="empty-state">
            <div class="empty-icon">👤</div>
            <p>No hay clientes registrados. Agrega los clientes de la pollada.</p>
        </div>
    <?php else: ?>
        <?php foreach ($clientes as $c): ?>
            <div class="list-item">
                <div style="background:var(--primary-bg);color:var(--primary);font-weight:700;font-size:12px;padding:4px 8px;border-radius:6px;font-family:monospace;letter-spacing:1px;flex-shrink:0;">
                    <?= $c['codigo_4digitos'] ?>
                </div>
                <div class="item-info">
                    <div class="item-name"><?= htmlspecialchars($c['nombre']) ?></div>
                    <div class="item-meta">
                        📍 <?= htmlspecialchars($c['direccion']) ?>
                        <?php if ($c['zona_nombre']): ?> · <?= $c['zona_nombre'] ?><?php endif; ?>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="btn-icon btn-outline sm" 
                            onclick="editCliente(<?= $c['id'] ?>, '<?= addslashes($c['nombre']) ?>', '<?= addslashes($c['direccion']) ?>', '<?= $c['codigo_4digitos'] ?>', '<?= $c['zona_entrega_id'] ?? '' ?>')">✏️</button>
                    <button class="btn-icon btn-outline sm" onclick="deleteCliente(<?= $c['id'] ?>)">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- FAB for new client -->
    <button class="fab" onclick="newCliente()" title="Nuevo Cliente">+</button>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- MODAL: Nuevo/Editar Cliente -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalCliente">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalClienteTitle">Nuevo Cliente</span>
            <button class="modal-close" onclick="closeModal('modalCliente')">✕</button>
        </div>
        <form id="formCliente">
            <input type="hidden" name="cliente_id" id="clienteId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" id="clienteNombre" placeholder="Nombre del cliente" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Dirección</label>
                    <input type="text" class="form-control" name="direccion" id="clienteDireccion" placeholder="Dirección de entrega" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Código (4 dígitos)</label>
                        <input type="text" class="form-control" name="codigo_4digitos" id="clienteCodigo" 
                               value="<?= $nextCode ?>" maxlength="4" pattern="\d{4}" required
                               style="font-family:monospace;font-size:18px;text-align:center;letter-spacing:4px">
                        <div class="form-hint">Asociado a tarjeta física</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Zona de Entrega</label>
                        <select class="form-control" name="zona_entrega_id" id="clienteZona">
                            <option value="">Sin zona</option>
                            <?php foreach ($zonas as $z): ?>
                                <option value="<?= $z['id'] ?>"><?= htmlspecialchars($z['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalCliente')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
