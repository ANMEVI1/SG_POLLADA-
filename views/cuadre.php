<?php
/**
 * Vista: Módulo de Cuadre de Caja y Ganancias
 * Tabs: Caja Actual | Historial
 */

// ─── DATA LOADING ───────────────────────────────────────────
$cajaAbierta = $pdo->query("SELECT * FROM cuadre_caja WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1")->fetch();
$historial = $pdo->query("SELECT * FROM cuadre_caja WHERE estado = 'cerrado' ORDER BY id DESC")->fetchAll();

// Stats en vivo (para caja en curso o por abrir)
$totalVentas = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE cuadre_caja_id IS NULL")->fetchColumn();
$totalEfectivo = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE estado_pago = 'efectivo' AND cuadre_caja_id IS NULL")->fetchColumn();
$totalYape = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE estado_pago = 'yape' AND cuadre_caja_id IS NULL")->fetchColumn();

$totalProd = $pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM producto WHERE comprado = 1 AND cuadre_caja_id IS NULL")->fetchColumn();
$totalEgr = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto WHERE cuadre_caja_id IS NULL")->fetchColumn();
$egresosDeCaja = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto WHERE salio_de_caja = 1 AND cuadre_caja_id IS NULL")->fetchColumn();

$gastoTotal = round($totalProd + $totalEgr, 2);
$gananciaNeta = round($totalVentas - $gastoTotal, 2);

$efectivoTeorico = round($totalEfectivo - $egresosDeCaja, 2);

$totalEntregas = $pdo->query("SELECT COUNT(*) FROM venta WHERE cuadre_caja_id IS NULL")->fetchColumn();
$totalArroz = $pdo->query("SELECT COUNT(*) FROM entrega_cliente ec JOIN venta v ON v.entrega_cliente_id = ec.id WHERE ec.con_arroz = 1 AND v.cuadre_caja_id IS NULL")->fetchColumn();
$totalPagados = $pdo->query("SELECT COUNT(*) FROM venta WHERE estado_pago != 'pendiente' AND cuadre_caja_id IS NULL")->fetchColumn();
$totalPendientes = $pdo->query("SELECT COUNT(*) FROM venta WHERE estado_pago = 'pendiente' AND cuadre_caja_id IS NULL")->fetchColumn();
$montoPagado = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE estado_pago != 'pendiente' AND cuadre_caja_id IS NULL")->fetchColumn();
$montoPendiente = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta WHERE estado_pago = 'pendiente' AND cuadre_caja_id IS NULL")->fetchColumn();

// Velocidad de venta
$primeraEntrega = $pdo->query("SELECT MIN(hora_entrega) FROM entrega_cliente WHERE hora_entrega IS NOT NULL")->fetchColumn();
$ultimaEntrega = $pdo->query("SELECT MAX(hora_entrega) FROM entrega_cliente WHERE hora_entrega IS NOT NULL")->fetchColumn();
$tiempoVenta = '';
if ($primeraEntrega && $ultimaEntrega && $totalEntregas > 0) {
    $diff = strtotime($ultimaEntrega) - strtotime($primeraEntrega);
    $horas = floor($diff / 3600);
    $minutos = floor(($diff % 3600) / 60);
    $tiempoVenta = ($horas > 0 ? $horas . 'h ' : '') . $minutos . 'min';
}
?>

<!-- Tabs -->
<div class="section-tabs">
    <button class="tab-pill active" data-tab="caja" data-group="cuadre">📋 Caja Actual</button>
    <button class="tab-pill" data-tab="historial" data-group="cuadre">📚 Historial</button>
    <button class="tab-pill" data-tab="config" data-group="cuadre">⚙️ Config</button>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: CAJA ACTUAL -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content active" id="tab-caja" data-group="cuadre">

    <!-- Status -->
    <div class="caja-status">
        <?php if ($cajaAbierta): ?>
            <div class="status-icon">🟢</div>
            <div class="status-text text-success">Jornada Abierta</div>
            <div class="status-time">
                Apertura: <?= date('h:i A', strtotime($cajaAbierta['hora_apertura'])) ?>
                · <?= date('d/m/Y', strtotime($cajaAbierta['hora_apertura'])) ?>
            </div>
            <?php
            $diffApertura = time() - strtotime($cajaAbierta['hora_apertura']);
            $hApertura = floor($diffApertura / 3600);
            $mApertura = floor(($diffApertura % 3600) / 60);
            ?>
            <div class="badge badge-success mt-8">
                Tiempo activo: <?= $hApertura ?>h <?= $mApertura ?>min
            </div>
        <?php else: ?>
            <div class="status-icon">🔴</div>
            <div class="status-text text-danger">Caja Cerrada</div>
            <div class="status-time">No hay jornada activa</div>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="mb-14">
        <?php if (!$cajaAbierta): ?>
            <button class="btn btn-success btn-block" onclick="solicitarApertura()">
                🟢 Abrir Jornada
            </button>
        <?php else: ?>
            <button class="btn btn-danger btn-block" onclick="openModal('modalTipoCierre')">
                🔴 Cerrar Jornada (Cuadrar)
            </button>
        <?php endif; ?>
    </div>

    <?php if ($cajaAbierta || $totalEntregas > 0): ?>
        <!-- Live summary card -->
        <div class="summary-card <?= $gananciaNeta >= 0 ? 'success-gradient' : '' ?>">
            <div class="summary-label">Ganancia Neta (en vivo)</div>
            <div class="summary-value">S/<?= number_format($gananciaNeta, 2) ?></div>
            <div class="summary-detail">
                Ventas: S/<?= number_format($totalVentas, 2) ?> - Gastos: S/<?= number_format($gastoTotal, 2) ?>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row cols-4 mb-12">
            <div class="stat-card">
                <div class="stat-value success"><?= $totalEntregas ?></div>
                <div class="stat-label">Entregados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value warning"><?= $totalArroz ?></div>
                <div class="stat-label">Con Arroz</div>
            </div>
            <div class="stat-card">
                <div class="stat-value info"><?= $totalPagados ?></div>
                <div class="stat-label">Pagados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value danger"><?= $totalPendientes ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>

        <!-- Detailed breakdown -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">💰 Detalle Financiero</span>
            </div>
            <div class="card-body">
                <div class="category-bar">
                    <span class="cat-name">Total Vendido</span>
                    <span class="cat-amount">S/<?= number_format($totalVentas, 2) ?></span>
                </div>
                <div class="category-bar">
                    <span class="cat-name">├─ Efectivo (Billetes/Monedas)</span>
                    <span class="cat-amount text-success">S/<?= number_format($totalEfectivo, 2) ?></span>
                </div>
                <div class="category-bar">
                    <span class="cat-name">├─ Yape / Plin</span>
                    <span class="cat-amount text-primary">S/<?= number_format($totalYape, 2) ?></span>
                </div>
                <div class="category-bar">
                    <span class="cat-name">└─ Por cobrar (Fiado)</span>
                    <span class="cat-amount text-danger">S/<?= number_format($montoPendiente, 2) ?></span>
                </div>
                <hr class="divider">
                <div class="category-bar">
                    <span class="cat-name">Gasto Total</span>
                    <span class="cat-amount">S/<?= number_format($gastoTotal, 2) ?></span>
                </div>
                <div class="category-bar">
                    <span class="cat-name">├─ Productos Insumos</span>
                    <span class="cat-amount">S/<?= number_format($totalProd, 2) ?></span>
                </div>
                <div class="category-bar">
                    <span class="cat-name">└─ Imprevistos</span>
                    <span class="cat-amount">S/<?= number_format($totalEgr, 2) ?></span>
                </div>
                <div class="category-bar" style="margin-left: 20px; font-size: 11px; color: var(--text-muted);">
                    <span class="cat-name">↳ Salieron de la caja:</span>
                    <span class="cat-amount">S/<?= number_format($egresosDeCaja, 2) ?></span>
                </div>
                
                <div class="summary-card mt-12" style="background:var(--card-bg);border:1px dashed var(--border-color)">
                    <div class="summary-label" style="color:var(--text)">EFECTIVO TEÓRICO EN CAJA (Billetes)</div>
                    <div class="summary-value" style="color:var(--primary);font-size:20px;">S/<?= number_format($efectivoTeorico, 2) ?></div>
                    <div class="summary-detail" style="color:var(--text-muted)">
                        Ventas Efectivo (S/<?= number_format($totalEfectivo, 2) ?>) - Egresos de caja (S/<?= number_format($egresosDeCaja, 2) ?>)
                    </div>
                </div>
                <?php if ($tiempoVenta): ?>
                <hr class="divider">
                <div class="category-bar">
                    <span class="cat-name">⏱️ Tiempo de venta</span>
                    <span class="cat-amount"><?= $tiempoVenta ?></span>
                </div>
                <div class="category-bar">
                    <span class="cat-name">Primera entrega</span>
                    <span class="cat-amount fs-sm"><?= $primeraEntrega ? date('h:i A', strtotime($primeraEntrega)) : '-' ?></span>
                </div>
                <div class="category-bar">
                    <span class="cat-name">Última entrega</span>
                    <span class="cat-amount fs-sm"><?= $ultimaEntrega ? date('h:i A', strtotime($ultimaEntrega)) : '-' ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: HISTORIAL DE CUADRES -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content" id="tab-historial" data-group="cuadre">
    <?php if (empty($historial)): ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <p>No hay cuadres registrados aún.</p>
        </div>
    <?php else: ?>
        <?php foreach ($historial as $h): 
            $duracion = '';
            if ($h['hora_apertura'] && $h['hora_cierre']) {
                $diff = strtotime($h['hora_cierre']) - strtotime($h['hora_apertura']);
                $hrs = floor($diff / 3600);
                $mins = floor(($diff % 3600) / 60);
                $duracion = $hrs . 'h ' . $mins . 'min';
            }
        ?>
            <div class="cuadre-card">
                <div class="cc-header">
                    <div>
                        <div class="fw-600" style="font-size:14px">
                            📅 <?= date('d/m/Y', strtotime($h['hora_apertura'])) ?>
                        </div>
                        <div class="fs-sm text-muted">
                            <?= date('h:i A', strtotime($h['hora_apertura'])) ?> → <?= date('h:i A', strtotime($h['hora_cierre'])) ?>
                            <?php if ($duracion): ?> (<?= $duracion ?>)<?php endif; ?>
                        </div>
                    </div>
                    <span class="badge badge-success">Cerrado</span>
                </div>
                <div class="cc-body">
                    <div class="cc-stat">
                        <span class="label">Ventas Totales</span>
                        <span class="value">S/<?= number_format($h['monto_total_ventas'], 2) ?></span>
                    </div>
                    <div class="cc-stat">
                        <span class="label">Gasto Total</span>
                        <span class="value text-danger">S/<?= number_format($h['gasto_total'], 2) ?></span>
                    </div>
                    <div class="cc-stat" style="padding-top:8px;border-top:1px solid var(--border-light);margin-top:4px;">
                        <span class="label fw-600">Ganancia Neta</span>
                        <span class="value positive" style="font-size:16px">S/<?= number_format($h['ganancia_neta'], 2) ?></span>
                    </div>
                    <hr class="divider">
                    <div class="stats-row cols-4" style="margin-bottom:0">
                        <div class="text-center">
                            <div class="fw-700" style="font-size:16px"><?= $h['total_entregas'] ?></div>
                            <div class="fs-sm text-muted">Entregas</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-700 text-warning" style="font-size:16px"><?= $h['total_con_arroz'] ?></div>
                            <div class="fs-sm text-muted">Arroz</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-700 text-success" style="font-size:16px"><?= $h['total_pagados'] ?></div>
                            <div class="fs-sm text-muted">Pagados</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-700 text-danger" style="font-size:16px"><?= $h['total_pendientes'] ?></div>
                            <div class="fs-sm text-muted">Pend.</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 15px; border-top: 1px dashed var(--border-light); padding-top: 15px;">
                        <a href="api/reporte_pdf.php?cuadre_id=<?= $h['id'] ?>" target="_blank" class="btn btn-outline" style="width: 100%; justify-content: center; font-size: 14px; padding: 10px; color: var(--primary); border-color: var(--primary);">📄 Ver Completo (PDF)</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: CONFIGURACIÓN -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content" id="tab-config" data-group="cuadre">
    <div class="card">
        <div class="card-header">
            <span class="card-title">🔐 Cambiar PIN de Cierre</span>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">PIN Actual</label>
                <input type="password" class="form-control" id="pinActual" maxlength="4" 
                       placeholder="****" style="text-align:center;letter-spacing:8px;font-size:18px">
            </div>
            <div class="form-group">
                <label class="form-label">Nuevo PIN (4 dígitos)</label>
                <input type="text" class="form-control" id="pinNuevo" maxlength="4" pattern="\d{4}"
                       placeholder="****" style="text-align:center;letter-spacing:8px;font-size:18px">
            </div>
            <button class="btn btn-primary btn-block" onclick="cambiarPin()">Actualizar PIN</button>
        </div>
    </div>
</div>


<script>
// Cambiar PIN
async function cambiarPin() {
    const pinActual = document.getElementById('pinActual').value;
    const pinNuevo = document.getElementById('pinNuevo').value;
    
    if (!pinActual || !pinNuevo) {
        showToast('Completa ambos campos', 'error');
        return;
    }
    
    const result = await apiCall('cuadre', 'update_pin', { pin_actual: pinActual, pin_nuevo: pinNuevo });
    if (result.success) {
        document.getElementById('pinActual').value = '';
        document.getElementById('pinNuevo').value = '';
    }
}
</script>
