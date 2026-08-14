/**
 * SG POLLADA - JavaScript Principal
 * Maneja: modales, AJAX, toggles, búsqueda, filtros, PIN, toasts
 */

// ============================================================
// TOAST NOTIFICATIONS
// ============================================================
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `${type === 'success' ? '✅' : '❌'} ${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// ============================================================
// MODAL MANAGEMENT
// ============================================================
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// ============================================================
// AJAX HELPER
// ============================================================
async function apiCall(page, action, data = {}) {
    const formData = new FormData();
    formData.append('action', action);
    for (const [key, value] of Object.entries(data)) {
        formData.append(key, value);
    }
    
    try {
        const response = await fetch(`index.php?page=${page}&api=1&action=${action}`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            if (result.message) showToast(result.message);
        } else {
            showToast(result.message || 'Error en la operación', 'error');
        }
        return result;
    } catch (error) {
        showToast('Error de conexión', 'error');
        console.error('API Error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// ============================================================
// TAB SWITCHING
// ============================================================
function switchTab(tabName, groupId) {
    const group = groupId ? document.getElementById(groupId) : document;
    
    // Update pills
    const pills = group ? group.parentElement.querySelectorAll('.tab-pill') : document.querySelectorAll('.tab-pill');
    pills.forEach(pill => {
        pill.classList.toggle('active', pill.dataset.tab === tabName);
    });
    
    // Update content
    const contents = document.querySelectorAll(`.tab-content[data-group="${groupId || 'default'}"]`);
    contents.forEach(content => {
        content.classList.toggle('active', content.id === `tab-${tabName}`);
    });
}

// Tab pill click handlers
document.addEventListener('click', (e) => {
    const pill = e.target.closest('.tab-pill');
    if (pill && pill.dataset.tab) {
        switchTab(pill.dataset.tab, pill.dataset.group || 'default');
    }
});

// ============================================================
// INVERSION MODULE
// ============================================================

// Toggle producto comprado
async function toggleComprado(id, el) {
    const result = await apiCall('inversion', 'toggle_comprado', { id });
    if (result.success) {
        const item = el.closest('.list-item');
        const isChecked = result.comprado == 1;
        item.classList.toggle('checked', isChecked);
        el.classList.toggle('checked', isChecked);
        el.innerHTML = isChecked ? '✓' : '';
        if (isChecked) el.classList.add('animate-check');
        updateInversionTotals();
    }
}

// Save producto
async function saveProducto(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const action = data.producto_id ? 'edit_producto' : 'add_producto';
    const result = await apiCall('inversion', action, data);
    if (result.success) {
        closeModal('modalProducto');
        location.reload();
    }
}

// Delete producto
async function deleteProducto(id) {
    if (!confirm('¿Eliminar este producto?')) return;
    const result = await apiCall('inversion', 'delete_producto', { id });
    if (result.success) location.reload();
}

// Edit producto
function editProducto(id, nombre, categoriaId, cantidad, kilo, precioUnitario) {
    document.getElementById('prodId').value = id;
    document.getElementById('prodNombre').value = nombre;
    document.getElementById('prodCategoria').value = categoriaId;
    document.getElementById('prodCantidad').value = cantidad || '';
    document.getElementById('prodKilo').value = kilo || '';
    document.getElementById('prodPrecio').value = precioUnitario;
    document.getElementById('modalProductoTitle').textContent = 'Editar Producto';
    openModal('modalProducto');
}

// Reset producto form
function newProducto() {
    document.getElementById('formProducto').reset();
    document.getElementById('prodId').value = '';
    document.getElementById('modalProductoTitle').textContent = 'Nuevo Producto';
    openModal('modalProducto');
}

// Save egreso
async function saveEgreso(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const action = data.egreso_id ? 'edit_egreso' : 'add_egreso';
    const result = await apiCall('inversion', action, data);
    if (result.success) {
        closeModal('modalEgreso');
        location.reload();
    }
}

// Delete egreso
async function deleteEgreso(id) {
    if (!confirm('¿Eliminar este egreso?')) return;
    const result = await apiCall('inversion', 'delete_egreso', { id });
    if (result.success) location.reload();
}

// New egreso
function newEgreso() {
    document.getElementById('formEgreso').reset();
    document.getElementById('egresoId').value = '';
    openModal('modalEgreso');
}

// Update totals (called after toggle)
async function updateInversionTotals() {
    const result = await apiCall('inversion', 'get_totales', {});
    if (result.success && result.data) {
        const el = document.getElementById('gastoTotal');
        if (el) el.textContent = 'S/' + parseFloat(result.data.gran_total).toFixed(2);
    }
}

// ============================================================
// PERSONAL MODULE
// ============================================================

async function saveReconocimiento(id, el) {
    const monto = el.value;
    await apiCall('personal', 'update_reconocimiento', { id, monto });
}

function newPersonal() {
    const form = document.getElementById('formPersonal');
    if (form) form.reset();
    openModal('modalPersonal');
}

async function savePersonal(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const result = await apiCall('personal', 'add_personal', data);
    if (result.success) {
        closeModal('modalPersonal');
        location.reload();
    }
}

// ============================================================
// ENTREGA MODULE
// ============================================================

// Save cliente
async function saveCliente(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const action = data.cliente_id ? 'edit_cliente' : 'add_cliente';
    const result = await apiCall('entrega', action, data);
    if (result.success) {
        closeModal('modalCliente');
        location.reload();
    }
}

// Delete cliente
async function deleteCliente(id) {
    if (!confirm('¿Eliminar este cliente y sus entregas?')) return;
    const result = await apiCall('entrega', 'delete_cliente', { id });
    if (result.success) location.reload();
}

// Edit cliente
function editCliente(id, nombre, direccion, codigo, zonaId) {
    document.getElementById('clienteId').value = id;
    document.getElementById('clienteNombre').value = nombre;
    document.getElementById('clienteDireccion').value = direccion;
    document.getElementById('clienteCodigo').value = codigo;
    document.getElementById('clienteZona').value = zonaId || '';
    document.getElementById('modalClienteTitle').textContent = 'Editar Cliente';
    openModal('modalCliente');
}

// New cliente
function newCliente() {
    const form = document.getElementById('formCliente');
    if (form) form.reset();
    document.getElementById('clienteId').value = '';
    document.getElementById('modalClienteTitle').textContent = 'Nuevo Cliente';
    
    // Get next code
    fetch('index.php?page=entrega&api=1&action=next_code', { method: 'POST' })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('clienteCodigo').value = data.code;
            }
        });
    
    openModal('modalCliente');
}

// Toggle entregado
async function toggleEntregado(id, el) {
    const result = await apiCall('entrega', 'toggle_entregado', { id });
    if (result.success) {
        const card = el.closest('.delivery-card');
        const isEntregado = result.entregado == 1;
        card.classList.toggle('entregado', isEntregado);
        el.classList.toggle('active-success', isEntregado);
        el.querySelector('.chip-text').textContent = isEntregado ? 'Entregado ✓' : 'Entregar';
        if (isEntregado) el.classList.add('animate-pulse');
        updateEntregaStats();
    }
}

// Toggle arroz
async function toggleArroz(id, el) {
    const result = await apiCall('entrega', 'toggle_arroz', { id });
    if (result.success) {
        const isActive = result.con_arroz == 1;
        el.classList.toggle('active-warning', isActive);
        updateEntregaStats();
    }
}

// Toggle pago
async function togglePago(id, el) {
    const result = await apiCall('entrega', 'toggle_pago', { id });
    if (result.success) {
        const isActive = result.estado_pago === 'pagado';
        el.classList.toggle('active-info', isActive);
        el.querySelector('.chip-text').textContent = isActive ? 'Pagado ✓' : 'Pagar';
        updateEntregaStats();
    }
}

// Update stats counters
async function updateEntregaStats() {
    const result = await apiCall('entrega', 'get_stats', {});
    if (result.success && result.data) {
        const d = result.data;
        const els = {
            'statTotal': d.total,
            'statEntregados': d.entregados,
            'statPendientes': d.pendientes,
            'statArroz': d.con_arroz,
            'statPagados': d.pagados,
            'statMonto': 'S/' + parseFloat(d.monto_total || 0).toFixed(2)
        };
        for (const [id, val] of Object.entries(els)) {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        }
    }
}

// Search deliveries
function searchEntregas(query) {
    query = query.toLowerCase().trim();
    document.querySelectorAll('.delivery-card').forEach(card => {
        const code = (card.dataset.code || '').toLowerCase();
        const name = (card.dataset.name || '').toLowerCase();
        const address = (card.dataset.address || '').toLowerCase();
        const match = !query || code.includes(query) || name.includes(query) || address.includes(query);
        card.style.display = match ? '' : 'none';
    });
}

// Filter deliveries
function filterEntregas(filter) {
    // Update active pill
    document.querySelectorAll('.filter-pill[data-filter]').forEach(pill => {
        pill.classList.toggle('active', pill.dataset.filter === filter);
    });
    
    document.querySelectorAll('.delivery-card').forEach(card => {
        let show = true;
        switch (filter) {
            case 'pendientes':
                show = !card.classList.contains('entregado');
                break;
            case 'entregados':
                show = card.classList.contains('entregado');
                break;
            case 'arroz':
                show = card.dataset.arroz === '1';
                break;
            case 'pagados':
                show = card.dataset.pagado === '1';
                break;
        }
        card.style.display = show ? '' : 'none';
    });
}

// Generate entregas from clientes
async function generarEntregas() {
    if (!confirm('¿Generar entregas para todos los clientes sin entrega asignada?')) return;
    const result = await apiCall('entrega', 'generar_entregas', {});
    if (result.success) location.reload();
}

// ============================================================
// CUADRE MODULE
// ============================================================

// Abrir caja
async function abrirCaja() {
    if (!confirm('¿Abrir jornada de caja?')) return;
    const result = await apiCall('cuadre', 'abrir_caja', {});
    if (result.success) location.reload();
}

let pinAction = '';

// Abrir caja - show PIN modal
function solicitarApertura() {
    pinAction = 'abrir_caja';
    document.getElementById('modalPinTitle').textContent = '🔐 Abrir Jornada';
    document.getElementById('modalPinDesc').textContent = 'Ingresa tu PIN para abrir la jornada';
    document.getElementById('btnSubmitPin').textContent = '🟢 Abrir Jornada';
    document.getElementById('btnSubmitPin').className = 'btn btn-success';
    
    // Clear inputs
    document.querySelectorAll('.pin-input').forEach(i => i.value = '');
    openModal('modalPin');
    setTimeout(() => {
        const firstInput = document.querySelector('.pin-input');
        if (firstInput) firstInput.focus();
    }, 300);
}

// Cerrar caja - show PIN modal
function solicitarCierre() {
    pinAction = 'cerrar_caja';
    document.getElementById('modalPinTitle').textContent = '🔐 Cerrar Jornada';
    document.getElementById('modalPinDesc').textContent = 'Ingresa tu PIN para cerrar la jornada';
    document.getElementById('btnSubmitPin').textContent = '🔴 Cerrar Jornada';
    document.getElementById('btnSubmitPin').className = 'btn btn-danger';
    
    // Clear inputs
    document.querySelectorAll('.pin-input').forEach(i => i.value = '');
    openModal('modalPin');
    setTimeout(() => {
        const firstInput = document.querySelector('.pin-input');
        if (firstInput) firstInput.focus();
    }, 300);
}

// Verify PIN and submit
async function submitPin() {
    const inputs = document.querySelectorAll('.pin-input');
    let pin = '';
    inputs.forEach(input => pin += input.value);
    
    if (pin.length !== 4) {
        showToast('Ingresa el PIN de 4 dígitos', 'error');
        return;
    }
    
    const result = await apiCall('cuadre', pinAction, { pin });
    if (result.success) {
        closeModal('modalPin');
        location.reload();
    }
}

// PIN input auto-focus
document.addEventListener('input', (e) => {
    if (e.target.classList.contains('pin-input')) {
        const value = e.target.value;
        if (value.length === 1) {
            const next = e.target.nextElementSibling;
            if (next && next.classList.contains('pin-input')) {
                next.focus();
            }
        }
    }
});

document.addEventListener('keydown', (e) => {
    if (e.target.classList.contains('pin-input') && e.key === 'Backspace' && !e.target.value) {
        const prev = e.target.previousElementSibling;
        if (prev && prev.classList.contains('pin-input')) {
            prev.focus();
        }
    }
});

// ============================================================
// FORM SUBMISSIONS
// ============================================================
document.addEventListener('submit', (e) => {
    const form = e.target;
    
    if (form.id === 'formProducto') {
        e.preventDefault();
        saveProducto(form);
    } else if (form.id === 'formEgreso') {
        e.preventDefault();
        saveEgreso(form);
    } else if (form.id === 'formCliente') {
        e.preventDefault();
        saveCliente(form);
    } else if (form.id === 'formPersonal') {
        e.preventDefault();
        savePersonal(form);
    }
});

// ============================================================
// AUTO-CALCULATE SUBTOTAL
// ============================================================
document.addEventListener('input', (e) => {
    if (['prodCantidad', 'prodKilo', 'prodPrecio'].includes(e.target.id)) {
        const cantidad = parseFloat(document.getElementById('prodCantidad')?.value) || 0;
        const kilo = parseFloat(document.getElementById('prodKilo')?.value) || 0;
        const precio = parseFloat(document.getElementById('prodPrecio')?.value) || 0;
        const factor = cantidad || kilo || 1;
        const subtotal = (factor * precio).toFixed(2);
        const el = document.getElementById('prodSubtotal');
        if (el) el.textContent = 'S/' + subtotal;
    }
});
