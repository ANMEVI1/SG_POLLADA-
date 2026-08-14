<?php
/**
 * Vista: Módulo de Personal y Dividendos
 */

// ─── DATA LOADING ───────────────────────────────────────────
$personal = $pdo->query("SELECT * FROM personal ORDER BY participacion, id")->fetchAll();

// Cálculos de dividendos
$totalVentas = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM venta")->fetchColumn();
$totalProd = $pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM producto WHERE comprado = 1")->fetchColumn();
$totalEgr = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto")->fetchColumn();
$gastoTotal = round($totalProd + $totalEgr, 2);
$totalReconocimientos = $pdo->query("SELECT COALESCE(SUM(reconocimiento_monetario), 0) FROM personal")->fetchColumn();
$gananciaBruta = round($totalVentas - $gastoTotal, 2);
$gananciaNeta = round($gananciaBruta - $totalReconocimientos, 2);
$inversionistas = array_filter($personal, fn($p) => $p['participacion'] === 'inversionista');
$numInversionistas = count($inversionistas);
$porPersona = $numInversionistas > 0 ? round($gananciaNeta / $numInversionistas, 2) : 0;

$iniciales = [
    'Sara' => 'S',
    'Andre' => 'A',
    'Sra. Charito' => 'C'
];
?>

<!-- Tabs -->
<div class="section-tabs">
    <button class="tab-pill active" data-tab="equipo" data-group="pers">👥 Equipo</button>
    <button class="tab-pill" data-tab="dividendos" data-group="pers">💰 Dividendos</button>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: EQUIPO -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content active" id="tab-equipo" data-group="pers">
    <?php foreach ($personal as $p): ?>
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-center gap-10">
                    <div style="width:40px;height:40px;border-radius:50%;background:var(--primary-bg);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--primary);font-size:16px;">
                        <?= $iniciales[$p['nombres']] ?? strtoupper(substr($p['nombres'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="card-title"><?= htmlspecialchars($p['nombres']) ?></div>
                        <div class="card-subtitle">
                            <span class="badge <?= $p['participacion'] === 'inversionista' ? 'badge-primary' : 'badge-info' ?>">
                                <?= ucfirst($p['participacion']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group mb-4">
                    <label class="form-label">Reconocimiento Monetario (S/)</label>
                    <input type="number" step="0.01" class="form-control" 
                           value="<?= $p['reconocimiento_monetario'] ?>"
                           onchange="saveReconocimiento(<?= $p['id'] ?>, this)"
                           placeholder="0.00">
                    <div class="form-hint">Se asigna al final de la jornada</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: DIVIDENDOS -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content" id="tab-dividendos" data-group="pers">
    
    <!-- Financial Summary -->
    <div class="card mb-14">
        <div class="card-header">
            <span class="card-title">📊 Resumen Financiero</span>
        </div>
        <div class="card-body">
            <div class="category-bar">
                <span class="cat-name">Total Ventas</span>
                <span class="cat-amount">S/<?= number_format($totalVentas, 2) ?></span>
            </div>
            <div class="category-bar">
                <span class="cat-name">(-) Gasto Total</span>
                <span class="cat-amount text-danger">S/<?= number_format($gastoTotal, 2) ?></span>
            </div>
            <div class="category-bar">
                <span class="cat-name">Ganancia Bruta</span>
                <span class="cat-amount fw-600">S/<?= number_format($gananciaBruta, 2) ?></span>
            </div>
            <div class="category-bar">
                <span class="cat-name">(-) Reconocimientos</span>
                <span class="cat-amount text-danger">S/<?= number_format($totalReconocimientos, 2) ?></span>
            </div>
            <hr class="divider">
            <div class="category-bar">
                <span class="cat-name fw-700" style="font-size:15px">Ganancia Neta</span>
                <span class="cat-amount fw-700 <?= $gananciaNeta >= 0 ? 'text-success' : 'text-danger' ?>" style="font-size:18px">
                    S/<?= number_format($gananciaNeta, 2) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Per-person dividend -->
    <div class="list-header">Reparto de Ganancias (Inversionistas)</div>
    
    <div class="stats-row">
        <?php foreach ($inversionistas as $inv): ?>
            <div class="dividend-card">
                <div class="person-avatar">
                    <?= $iniciales[$inv['nombres']] ?? strtoupper(substr($inv['nombres'], 0, 1)) ?>
                </div>
                <div class="person-name"><?= htmlspecialchars($inv['nombres']) ?></div>
                <div class="person-role">Inversionista</div>
                <div class="person-amount <?= $porPersona >= 0 ? '' : 'text-danger' ?>">
                    S/<?= number_format($porPersona, 2) ?>
                </div>
                <div class="person-amount-label">Ganancia neta</div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Ayudantes -->
    <?php 
    $ayudantes = array_filter($personal, fn($p) => $p['participacion'] === 'ayudante');
    if (!empty($ayudantes)):
    ?>
    <div class="list-header mt-12">Reconocimientos (Ayudantes)</div>
    <?php foreach ($ayudantes as $ay): ?>
        <div class="card">
            <div class="d-flex justify-between align-center">
                <div class="d-flex align-center gap-8">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--info-bg);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--info);font-size:14px;">
                        <?= $iniciales[$ay['nombres']] ?? strtoupper(substr($ay['nombres'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-600" style="font-size:14px"><?= htmlspecialchars($ay['nombres']) ?></div>
                        <div class="fs-sm text-muted">Ayudante</div>
                    </div>
                </div>
                <div class="fw-700 text-primary" style="font-size:16px">
                    S/<?= number_format($ay['reconocimiento_monetario'], 2) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
