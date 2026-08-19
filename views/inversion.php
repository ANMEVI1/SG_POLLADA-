<?php
/**
 * Vista: Módulo de Inversión (Presupuesto)
 * Tabs: Productos | Egresos | Resumen
 */

// ─── DATA LOADING ───────────────────────────────────────────
$categorias = $pdo->query("SELECT * FROM categoria WHERE activo = 1 ORDER BY id")->fetchAll();
$productos = $pdo->query("
    SELECT p.*, c.nombre AS categoria_nombre 
    FROM producto p 
    JOIN categoria c ON p.categoria_id = c.id 
    WHERE p.archivado = 0
    ORDER BY p.comprado ASC, c.nombre, p.nombre
")->fetchAll();
$egresos = $pdo->query("
    SELECT e.*, c.nombre AS categoria_nombre 
    FROM egreso_imprevisto e 
    JOIN categoria c ON e.categoria_id = c.id 
    WHERE e.archivado = 0
    ORDER BY e.created_at DESC
")->fetchAll();

// Totales
$totalProd = $pdo->query("SELECT COALESCE(SUM(subtotal), 0) FROM producto WHERE comprado = 1 AND archivado = 0")->fetchColumn();
$totalEgr = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM egreso_imprevisto WHERE archivado = 0")->fetchColumn();
$granTotal = round($totalProd + $totalEgr, 2);

// Totales por categoría
$porCategoria = $pdo->query("
    SELECT c.nombre, 
           COALESCE(SUM(p.subtotal), 0) AS total_prod,
           COALESCE((SELECT SUM(e.monto) FROM egreso_imprevisto e WHERE e.categoria_id = c.id), 0) AS total_egr
    FROM categoria c
    LEFT JOIN producto p ON p.categoria_id = c.id AND p.comprado = 1
    WHERE c.activo = 1
    GROUP BY c.id, c.nombre
    ORDER BY c.id
")->fetchAll();
?>

<!-- Summary Card -->
<div class="summary-card">
    <div class="summary-label">Gasto Total Acumulado</div>
    <div class="summary-value" id="gastoTotal">S/<?= number_format($granTotal, 2) ?></div>
    <div class="summary-detail">Productos: S/<?= number_format($totalProd, 2) ?> · Egresos: S/<?= number_format($totalEgr, 2) ?></div>
</div>

<!-- Tabs -->
<div class="section-tabs">
    <button class="tab-pill active" data-tab="productos" data-group="inv">📦 Productos</button>
    <button class="tab-pill" data-tab="egresos" data-group="inv">💸 Egresos</button>
    <button class="tab-pill" data-tab="resumen" data-group="inv">📊 Resumen</button>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: PRODUCTOS -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content active" id="tab-productos" data-group="inv">
    <div class="d-flex justify-between align-center mb-12">
        <span class="fs-sm text-muted"><?= count($productos) ?> productos registrados</span>
        <button class="btn btn-primary btn-sm" onclick="newProducto()">+ Agregar</button>
    </div>

    <!-- Search Bar Experto (Sticky) -->
    <div class="search-bar" style="position: sticky; top: 0; z-index: 10; background: var(--bg-color); padding: 10px 0; border-bottom: 1px solid rgba(0,0,0,0.05); margin-bottom: 16px;">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Buscar producto o categoría..." 
               oninput="searchProductos(this.value)" id="searchProductosInput" style="box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    </div>

    <?php if (empty($productos)): ?>
        <div class="empty-state" id="productosEmptyState">
            <div class="empty-icon">📦</div>
            <p>No hay productos registrados. Agrega los insumos de tu pollada.</p>
        </div>
        <div id="productosList"></div>
    <?php else: ?>
        <div class="empty-state" id="productosEmptyState" style="display:none;">
            <div class="empty-icon">📦</div>
            <p>No hay coincidencias en tu búsqueda.</p>
        </div>
        <div id="productosList">
        <?php 
        $currentCat = '';
        foreach ($productos as $p): 
            if ($p['categoria_nombre'] !== $currentCat):
                $currentCat = $p['categoria_nombre'];
        ?>
            <div class="list-header product-header" data-category="<?= htmlspecialchars(strtolower($currentCat)) ?>"><?= htmlspecialchars($currentCat) ?></div>
        <?php endif; ?>
            <div class="list-item product-item <?= $p['comprado'] ? 'checked' : '' ?>"
                 data-name="<?= htmlspecialchars(strtolower($p['nombre'])) ?>"
                 data-category="<?= htmlspecialchars(strtolower($p['categoria_nombre'])) ?>">
                <div class="item-check <?= $p['comprado'] ? 'checked' : '' ?>" 
                     onclick="toggleComprado(<?= $p['id'] ?>, this)">
                    <?= $p['comprado'] ? '✓' : '' ?>
                </div>
                <div class="item-info">
                    <div class="item-name"><?= htmlspecialchars($p['nombre']) ?></div>
                    <div class="item-meta">
                        <?php if ($p['cantidad']): ?>
                            Cant: <?= $p['cantidad'] ?>
                        <?php endif; ?>
                        <?php if ($p['kilo']): ?>
                            <?= $p['cantidad'] ? ' · ' : '' ?>Kg: <?= $p['kilo'] ?>
                        <?php endif; ?>
                        <?php if ($p['fecha_compra']): ?>
                            · <?= date('d/m', strtotime($p['fecha_compra'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="item-price">S/<?= number_format($p['subtotal'], 2) ?></div>
                <div class="item-actions">
                    <button class="btn-icon btn-outline sm" 
                            onclick="editProducto(<?= $p['id'] ?>, '<?= addslashes($p['nombre']) ?>', <?= $p['categoria_id'] ?>, '<?= $p['cantidad'] ?>', '<?= $p['kilo'] ?>', '<?= $p['precio_unitario'] ?>')">✏️</button>
                    <button class="btn-icon btn-outline sm" onclick="deleteProducto(<?= $p['id'] ?>)">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: EGRESOS IMPREVISTOS -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content" id="tab-egresos" data-group="inv">
    <div class="d-flex justify-between align-center mb-12">
        <span class="fs-sm text-muted"><?= count($egresos) ?> egresos registrados</span>
        <button class="btn btn-primary btn-sm" onclick="newEgreso()">+ Agregar</button>
    </div>

    <!-- Search Bar Experto (Sticky) -->
    <div class="search-bar" style="position: sticky; top: 0; z-index: 10; background: var(--bg-color); padding: 10px 0; border-bottom: 1px solid rgba(0,0,0,0.05); margin-bottom: 16px;">
        <span class="search-icon">🔍</span>
        <input type="text" placeholder="Buscar egreso..." 
               oninput="searchEgresos(this.value)" id="searchEgresosInput" style="box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    </div>

    <?php if (empty($egresos)): ?>
        <div class="empty-state" id="egresosEmptyState">
            <div class="empty-icon">💸</div>
            <p>No hay egresos imprevistos registrados.</p>
        </div>
        <div id="egresosList"></div>
    <?php else: ?>
        <div class="empty-state" id="egresosEmptyState" style="display:none;">
            <div class="empty-icon">💸</div>
            <p>No hay coincidencias en tu búsqueda.</p>
        </div>
        <div id="egresosList">
        <?php foreach ($egresos as $e): ?>
            <div class="list-item egreso-item" 
                 data-name="<?= htmlspecialchars(strtolower($e['descripcion'])) ?>"
                 data-category="<?= htmlspecialchars(strtolower($e['categoria_nombre'])) ?>">
                <div class="item-info">
                    <div class="item-name"><?= htmlspecialchars($e['descripcion']) ?></div>
                    <div class="item-meta">
                        <?= date('d/m', strtotime($e['fecha'])) ?> · <?= htmlspecialchars($e['categoria_nombre']) ?>
                        <?php if ($e['salio_de_caja']): ?>
                            <span class="badge badge-warning" style="font-size:10px;margin-left:4px">Salió de caja</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="item-actions">
                    <span class="fw-700 text-danger mr-8">S/<?= number_format($e['monto'], 2) ?></span>
                    <button class="btn-icon btn-outline sm" onclick="editEgreso(<?= $e['id'] ?>, '<?= addslashes($e['descripcion']) ?>', <?= $e['categoria_id'] ?>, <?= $e['monto'] ?>, '<?= $e['fecha'] ?>', <?= $e['salio_de_caja'] ?>)">✏️</button>
                    <button class="btn-icon btn-outline sm" onclick="deleteEgreso(<?= $e['id'] ?>)">🗑️</button>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TAB: RESUMEN POR CATEGORÍA -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="tab-content" id="tab-resumen" data-group="inv">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Desglose por Categoría</span>
        </div>
        <?php foreach ($porCategoria as $cat): 
            $catTotal = round($cat['total_prod'] + $cat['total_egr'], 2);
            $porcentaje = $granTotal > 0 ? round(($catTotal / $granTotal) * 100) : 0;
        ?>
            <div class="category-bar">
                <div style="flex:1">
                    <div class="cat-name"><?= htmlspecialchars($cat['nombre']) ?></div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= $porcentaje ?>%"></div>
                    </div>
                </div>
                <div class="cat-amount" style="margin-left:12px">
                    S/<?= number_format($catTotal, 2) ?>
                    <div class="fs-sm text-muted text-right"><?= $porcentaje ?>%</div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value"><?= count(array_filter($productos, fn($p) => $p['comprado'])) ?></div>
            <div class="stat-label">Comprados</div>
        </div>
        <div class="stat-card">
            <div class="stat-value warning"><?= count(array_filter($productos, fn($p) => !$p['comprado'])) ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- MODAL: Nuevo/Editar Producto -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalProducto">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title" id="modalProductoTitle">Nuevo Producto</span>
            <button class="modal-close" onclick="closeModal('modalProducto')">✕</button>
        </div>
        <form id="formProducto">
            <input type="hidden" name="producto_id" id="prodId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nombre del producto</label>
                    <input type="text" class="form-control" name="nombre" id="prodNombre" placeholder="Ej: Pollo" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select class="form-control" name="categoria_id" id="prodCategoria" required>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Cantidad</label>
                        <input type="number" step="0.01" class="form-control" name="cantidad" id="prodCantidad" placeholder="Opcional">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kilos</label>
                        <input type="number" step="0.001" class="form-control" name="kilo" id="prodKilo" placeholder="Opcional">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Precio Unitario (S/)</label>
                    <input type="number" step="0.01" class="form-control" name="precio_unitario" id="prodPrecio" placeholder="0.00" required>
                </div>
                <div class="d-flex justify-between align-center mt-8" style="padding:8px 0">
                    <span class="text-muted fs-sm">Subtotal estimado:</span>
                    <span class="fw-700 text-primary" id="prodSubtotal">S/0.00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalProducto')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- MODAL: Nuevo Egreso -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalEgreso">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Nuevo Egreso Imprevisto</span>
            <button class="modal-close" onclick="closeModal('modalEgreso')">✕</button>
        </div>
        <form id="formEgreso">
            <input type="hidden" name="egreso_id" id="egresoId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <input type="text" class="form-control" name="descripcion" placeholder="Ej: Pasajes ida y vuelta" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select class="form-control" name="categoria_id" required>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monto (S/)</label>
                        <input type="number" class="form-control" name="monto" id="egresoMonto" step="0.10" required>
                    </div>
                    <div class="form-group mt-12">
                        <label class="d-flex align-center gap-8" style="cursor:pointer">
                            <input type="checkbox" name="salio_de_caja" id="egresoCaja" value="1" checked style="width:18px;height:18px;">
                            <div>
                                <div class="fw-600">Salió de Caja</div>
                                <div class="fs-sm text-muted">El dinero se sacó del canguro de ventas.</div>
                            </div>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEgreso')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
