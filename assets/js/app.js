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
    document.getElementById('egresoCaja').checked = true;
    openModal('modalEgreso');
}

// Edit egreso
function editEgreso(id, descripcion, categoriaId, monto, fecha, salioCaja) {
    document.getElementById('egresoId').value = id;
    document.querySelector('#formEgreso [name="descripcion"]').value = descripcion;
    document.querySelector('#formEgreso [name="categoria_id"]').value = categoriaId;
    document.getElementById('egresoMonto').value = monto;
    document.querySelector('#formEgreso [name="fecha"]').value = fecha ? fecha.substring(0, 10) : '';
    document.getElementById('egresoCaja').checked = salioCaja == 1;
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
    document.getElementById('personalId').value = '';
    document.querySelector('#modalPersonal .modal-title').textContent = 'Añadir Personal';
    openModal('modalPersonal');
}

function editPersonal(id, nombres, participacion) {
    document.getElementById('personalId').value = id;
    document.querySelector('#formPersonal [name="nombres"]').value = nombres;
    document.querySelector('#formPersonal [name="participacion"]').value = participacion;
    document.querySelector('#modalPersonal .modal-title').textContent = 'Editar Personal';
    openModal('modalPersonal');
}

function deletePersonal(id) {
    if (!confirm('¿Eliminar este miembro del equipo?')) return;

    pinModule = 'personal';
    pinAction = 'delete_personal';
    pinData = { id };
    pinCallback = () => location.reload();

    document.getElementById('modalPinTitle').textContent = '🔐 Eliminar Personal';
    document.getElementById('modalPinDesc').textContent = 'Ingresa PIN para anular a este miembro';
    document.getElementById('btnSubmitPin').textContent = 'Eliminar';
    document.getElementById('btnSubmitPin').className = 'btn btn-danger';
    document.getElementById('modalPinExtra').style.display = 'none';

    document.querySelectorAll('.pin-input').forEach(i => i.value = '');
    openModal('modalPin');
    setTimeout(() => {
        const firstInput = document.querySelector('.pin-input');
        if (firstInput) firstInput.focus();
    }, 300);
}

function savePersonal(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    if (data.personal_id) {
        // Edit requires PIN
        data.id = data.personal_id;

        pinModule = 'personal';
        pinAction = 'edit_personal';
        pinData = data;
        pinCallback = () => {
            closeModal('modalPersonal');
            location.reload();
        };

        document.getElementById('modalPinTitle').textContent = '🔐 Editar Personal';
        document.getElementById('modalPinDesc').textContent = 'Ingresa PIN para autorizar la edición';
        document.getElementById('btnSubmitPin').textContent = 'Guardar Cambios';
        document.getElementById('btnSubmitPin').className = 'btn btn-primary';
        document.getElementById('modalPinExtra').style.display = 'none';

        document.querySelectorAll('.pin-input').forEach(i => i.value = '');
        openModal('modalPin');
        setTimeout(() => {
            const firstInput = document.querySelector('.pin-input');
            if (firstInput) firstInput.focus();
        }, 300);
    } else {
        // Add does not require PIN
        apiCall('personal', 'add_personal', data).then(result => {
            if (result.success) {
                closeModal('modalPersonal');
                location.reload();
            }
        });
    }
}

// ============================================================
// ENTREGA MODULE
// ============================================================

async function saveCliente(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    const isEdit = !!data.cliente_id;
    const action = isEdit ? 'edit_cliente' : 'add_cliente';
    const actionType = document.getElementById('clienteActionType') ? document.getElementById('clienteActionType').value : 'salir';

    const result = await apiCall('entrega', action, data);
    if (result.success && result.html) {
        const listContainer = document.getElementById('clientesList');
        const emptyState = document.getElementById('clientesEmptyState');

        if (emptyState) emptyState.style.display = 'none';

        if (isEdit) {
            const existingItem = document.getElementById('cliente-' + result.id);
            if (existingItem) existingItem.outerHTML = result.html;
        } else {
            if (listContainer) listContainer.insertAdjacentHTML('beforeend', result.html);

            const countSpan = document.querySelector('#tab-clientes .fs-sm.text-muted');
            if (countSpan) {
                const match = countSpan.textContent.match(/\d+/);
                const count = match ? parseInt(match[0]) : 0;
                countSpan.textContent = `${count + 1} clientes registrados`;
            }
        }

        if (actionType === 'salir' || isEdit) {
            closeModal('modalCliente');
        } else {
            // Guardar y continuar agregando
            document.getElementById('clienteNombre').value = '';
            document.getElementById('clienteDireccion').value = '';
            document.getElementById('clienteNombre').focus();

            // Consultar a la BD el código secuencial estricto (+1 real)
            const codeResult = await apiCall('entrega', 'next_code');
            if (codeResult && codeResult.success) {
                document.getElementById('clienteCodigo').value = codeResult.code;
            }
        }

        // Show the generate deliveries FAB in Entregas tab
        const fab = document.getElementById('fabGenerar');
        if (fab) fab.style.display = 'flex';
    }
}

// Delete cliente
async function deleteCliente(id) {
    if (!confirm('¿Eliminar este cliente y sus entregas?')) return;
    const result = await apiCall('entrega', 'delete_cliente', { id });
    if (result.success) {
        const item = document.getElementById('cliente-' + id);
        if (item) item.remove();

        const countSpan = document.querySelector('#tab-clientes .fs-sm.text-muted');
        if (countSpan) {
            const match = countSpan.textContent.match(/\d+/);
            const count = match ? parseInt(match[0]) : 1;
            countSpan.textContent = `${count - 1} clientes registrados`;

            if (count - 1 <= 0) {
                const emptyState = document.getElementById('clientesEmptyState');
                if (emptyState) emptyState.style.display = 'flex';
            }
        }
    }
}

// ─── TICKETS WHATSAPP ───────────────────────────────────────
let waCurrentId = null;
let waCurrentName = '';
let waCurrentCode = '';

function abrirModalTicket(id, codigo, nombre) {
    waCurrentId = id;
    waCurrentName = nombre;
    waCurrentCode = codigo;

    document.getElementById('waTicketCode').textContent = codigo;
    document.getElementById('waTicketName').textContent = nombre;
    document.getElementById('waPhoneNumber').value = '';

    // Reset buttons
    const btnD = document.getElementById('btnDescargarTicket');
    const btnW = document.getElementById('btnEnviarWA');
    if (btnD) { btnD.innerHTML = '⬇️ 1. Descargar Ticket'; btnD.disabled = false; }
    if (btnW) { btnW.disabled = true; btnW.style.opacity = '0.5'; }

    openModal('modalWhatsAppTicket');
    setTimeout(() => document.getElementById('waPhoneNumber').focus(), 300);
}

async function descargarTicketPDF(btn) {
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Descargando... ⏳';
    btn.disabled = true;

    try {
        const baseUrl = window.location.origin + window.location.pathname.replace('index.php', '');
        const ticketUrl = baseUrl + 'api/ticket_pdf.php?id=' + waCurrentId;

        const response = await fetch(ticketUrl);
        if (!response.ok) throw new Error('Error en el servidor');

        const blob = await response.blob();
        const objectUrl = window.URL.createObjectURL(blob);
        const downloadLink = document.createElement('a');
        downloadLink.href = objectUrl;

        const cleanName = waCurrentName.replace(/[^A-Za-z0-9_\-]/g, '').replace(/\s+/g, '_');
        downloadLink.download = `Ticket_${waCurrentCode}_${cleanName}.pdf`;

        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);

        btn.innerHTML = '✅ Ticket Descargado';

        // Desbloquear botón WhatsApp
        const btnW = document.getElementById('btnEnviarWA');
        if (btnW) {
            btnW.disabled = false;
            btnW.style.opacity = '1';
        }

    } catch (error) {
        showToast('Ocurrió un error al descargar', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

function abrirChatWhatsApp() {
    const phone = document.getElementById('waPhoneNumber').value.trim();
    if (!phone) {
        showToast('Ingresa el número primero', 'error');
        document.getElementById('waPhoneNumber').focus();
        return;
    }

    const text = `Hola ${waCurrentName}, gracias por tu apoyo. Aquí te envío adjunto tu ticket para la pollada 🍗🎫`;
    const fullPhone = '+51' + phone;
    const waUrl = `https://wa.me/${fullPhone}?text=${encodeURIComponent(text)}`;

    window.open(waUrl, '_blank');
    closeModal('modalWhatsAppTicket');
}

// Edit cliente
function editCliente(id, nombre, direccion, codigo, zonaId, colocadoPor, parteAve, quiereArroz) {
    document.getElementById('clienteId').value = id;
    document.getElementById('clienteNombre').value = nombre;
    document.getElementById('clienteColocadoPor').value = colocadoPor || '';
    document.getElementById('clienteDireccion').value = direccion;
    document.getElementById('clienteCodigo').value = codigo;
    document.getElementById('clienteZona').value = zonaId || '';
    document.getElementById('clienteParteAve').value = parteAve || '';
    document.getElementById('clienteQuiereArroz').value = quiereArroz || '0';
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
function toggleEntregado(id, isEntregado, el) {
    if (isEntregado) {
        // Desmarcar -> Requiere PIN
        pinModule = 'entrega';
        pinAction = 'toggle_entregado';
        pinData = { id };
        pinCallback = (res) => {
            const card = el.closest('.delivery-card');
            card.classList.remove('entregado');
            card.dataset.deudor = '0';
            card.dataset.pagado = '0';

            el.classList.remove('active-success');
            el.querySelector('.chip-text').textContent = 'Entregar';
            el.setAttribute('onclick', `toggleEntregado(${id}, false, this)`);

            const btnPago = card.querySelectorAll('.toggle-chip')[2];
            if (btnPago) {
                btnPago.className = 'toggle-chip';
                btnPago.innerHTML = `💵 <span class="chip-text">Pagar</span>`;
                btnPago.setAttribute('onclick', `togglePago(${id}, '', this)`);
            }

            closeModal('modalPin');
            updateEntregaStats();
            checkCardVisibility(card);
        };

        document.getElementById('modalPinTitle').textContent = '🔐 Revertir Entrega';
        document.getElementById('modalPinDesc').textContent = 'Ingresa PIN para anular esta entrega';
        document.getElementById('btnSubmitPin').textContent = 'Anular Entrega';
        document.getElementById('btnSubmitPin').className = 'btn btn-danger';
        document.getElementById('modalPinExtra').style.display = 'none';

        document.querySelectorAll('.pin-input').forEach(i => i.value = '');
        openModal('modalPin');
        setTimeout(() => {
            const firstInput = document.querySelector('.pin-input');
            if (firstInput) firstInput.focus();
        }, 300);
    } else {
        // Marcar -> Sin PIN
        apiCall('entrega', 'toggle_entregado', { id }).then(res => {
            if (res.success) {
                const card = el.closest('.delivery-card');
                card.classList.add('entregado');
                card.dataset.deudor = '1';
                card.dataset.pagado = '0';

                el.classList.add('active-success');
                el.querySelector('.chip-text').textContent = 'Entregado ✓';
                el.setAttribute('onclick', `toggleEntregado(${id}, true, this)`);

                const btnPago = card.querySelectorAll('.toggle-chip')[2];
                if (btnPago) {
                    btnPago.className = 'toggle-chip';
                    btnPago.innerHTML = `💵 <span class="chip-text">Pagar</span>`;
                    btnPago.setAttribute('onclick', `togglePago(${id}, 'pendiente', this)`);
                }

                updateEntregaStats();
                checkCardVisibility(card);
            }
        });
    }
}

// Toggle arroz
async function toggleArroz(id, el) {
    const result = await apiCall('entrega', 'toggle_arroz', { id });
    if (result.success) {
        const isActive = result.con_arroz == 1;
        el.classList.toggle('active-warning', isActive);
        updateEntregaStats();
        const card = el.closest('.delivery-card');
        if (card) checkCardVisibility(card);
    }
}

// Intercept pago para mostrar modal de Yape
function togglePago(id, currentState, el) {
    if (!currentState || currentState === '') {
        showToast('Primero marca la pollada como Entregada', 'error');
        return;
    }
    if (currentState === 'yape') {
        // Revertir a pendiente -> Requiere PIN
        pinModule = 'entrega';
        pinAction = 'toggle_pago';
        pinData = { id };
        pinCallback = (res) => {
            const card = el.closest('.delivery-card');
            card.dataset.deudor = '1';
            card.dataset.pagado = '0';

            el.className = 'toggle-chip';
            el.innerHTML = `💵 <span class="chip-text">Pagar</span>`;
            el.setAttribute('onclick', `togglePago(${id}, 'pendiente', this)`);

            closeModal('modalPin');
            updateEntregaStats();
            checkCardVisibility(card);
        };

        document.getElementById('modalPinTitle').textContent = '🔐 Revertir Pago';
        document.getElementById('modalPinDesc').textContent = 'Ingresa PIN para anular este pago';
        document.getElementById('btnSubmitPin').textContent = 'Anular Pago';
        document.getElementById('btnSubmitPin').className = 'btn btn-danger';
        document.getElementById('modalPinExtra').style.display = 'none';

        document.querySelectorAll('.pin-input').forEach(i => i.value = '');
        openModal('modalPin');
        setTimeout(() => {
            const firstInput = document.querySelector('.pin-input');
            if (firstInput) firstInput.focus();
        }, 300);
    } else if (currentState === 'efectivo') {
        // Interceptar paso de efectivo -> yape para mostrar modal QR
        window.currentPagoId = id;
        window.currentPagoEl = el;
        currentQrIndex = 1; // 1 = Yape
        updateQRView();
        openModal('modalPagoQR');
    } else {
        // Marcar -> Sin PIN (pendiente -> efectivo)
        doTogglePagoAPI(id, el);
    }
}

// Logic for QR Carousel
const qrImages = [
    { src: 'assets/qr/pagos_qr/metodo_lemon.jpeg', label: 'Billetera Móvil / Lemon / Dale' },
    { src: 'assets/qr/pagos_qr/metodo_yape.jpeg', label: 'Yape / Plin' }
];
let currentQrIndex = 1; // Default to Yape

function updateQRView() {
    const img = document.getElementById('pagoQRImg');
    const label = document.getElementById('pagoQRLabel');
    if (img && label) {
        img.style.opacity = 0;
        setTimeout(() => {
            img.src = qrImages[currentQrIndex].src;
            label.textContent = qrImages[currentQrIndex].label;
            img.style.opacity = 1;
        }, 150);
    }
}

function nextQR() {
    currentQrIndex = (currentQrIndex + 1) % qrImages.length;
    updateQRView();
}

function prevQR() {
    currentQrIndex = (currentQrIndex - 1 + qrImages.length) % qrImages.length;
    updateQRView();
}

function confirmarPagoQR() {
    closeModal('modalPagoQR');
    if (window.currentPagoId && window.currentPagoEl) {
        doTogglePagoAPI(window.currentPagoId, window.currentPagoEl);
    }
}

function doTogglePagoAPI(id, el) {
    apiCall('entrega', 'toggle_pago', { id }).then(res => {
        if (res.success) {
            const newState = res.estado_pago;
            const card = el.closest('.delivery-card');
            card.dataset.deudor = '0';
            card.dataset.pagado = '1';

            if (newState === 'efectivo') {
                el.className = 'toggle-chip active-success';
                el.innerHTML = `💵 <span class="chip-text">Efectivo</span>`;
            } else if (newState === 'yape') {
                el.className = 'toggle-chip active-primary';
                el.innerHTML = `📱 <span class="chip-text">Yape</span>`;
            }

            el.setAttribute('onclick', `togglePago(${id}, '${newState}', this)`);
            updateEntregaStats();
            checkCardVisibility(card);
        }
    });
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
            'statDeudores': d.deudores,
            'statEsperado': 'S/' + parseFloat(d.monto_esperado || 0).toFixed(2),
            'statRecaudado': 'S/' + parseFloat(d.monto_recaudado || 0).toFixed(2)
        };
        for (const [id, val] of Object.entries(els)) {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        }

        // Update filter pill counts
        const pillMap = {
            'todos': d.total,
            'pendientes': d.pendientes,
            'entregados': d.entregados,
            'arroz': d.con_arroz,
            'pagados': d.pagados,
            'deudores': d.deudores
        };
        for (const [filterKey, val] of Object.entries(pillMap)) {
            const pillSpan = document.querySelector(`.filter-pill[data-filter="${filterKey}"] .filter-count`);
            if (pillSpan) pillSpan.textContent = val;
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

// Search Clientes (Experto)
function searchClientes(query) {
    query = query.toLowerCase().trim();
    let hasMatch = false;
    let visibleCount = 0;
    const totalCount = document.querySelectorAll('.client-item').length;

    document.querySelectorAll('.client-item').forEach(item => {
        const code = item.dataset.code || '';
        const name = item.dataset.name || '';
        const zona = item.dataset.zona || '';
        const match = !query || code.includes(query) || name.includes(query) || zona.includes(query);

        if (match) {
            item.classList.remove('search-hidden');
            hasMatch = true;
            visibleCount++;
        } else {
            item.classList.add('search-hidden');
        }
    });

    const emptyState = document.getElementById('clientesEmptyState');
    if (emptyState) emptyState.style.display = (!hasMatch && query) ? 'flex' : 'none';

    // Update counter
    const countSpan = document.querySelector('#tab-clientes .fs-sm.text-muted');
    if (countSpan) {
        if (query) {
            countSpan.textContent = `Mostrando ${visibleCount} de ${totalCount} clientes`;
        } else {
            countSpan.textContent = `${totalCount} clientes registrados`;
        }
    }
}

// Search Productos (Experto)
function searchProductos(query) {
    query = query.toLowerCase().trim();
    let hasMatch = false;

    // Reset all headers first
    const headers = document.querySelectorAll('.product-header');
    headers.forEach(h => h.classList.remove('search-hidden'));

    document.querySelectorAll('.product-item').forEach(item => {
        const name = item.dataset.name || '';
        const cat = item.dataset.category || '';
        const match = !query || name.includes(query) || cat.includes(query);

        if (match) {
            item.classList.remove('search-hidden');
            hasMatch = true;
        } else {
            item.classList.add('search-hidden');
        }
    });

    // Hide empty headers
    if (query) {
        headers.forEach(header => {
            const cat = header.dataset.category;
            const visibleProducts = document.querySelectorAll(`.product-item[data-category="${cat}"]:not(.search-hidden)`);
            if (visibleProducts.length === 0) {
                header.classList.add('search-hidden');
            }
        });
    }

    const emptyState = document.getElementById('productosEmptyState');
    if (emptyState) emptyState.style.display = (!hasMatch && query) ? 'flex' : 'none';
}

// Search Egresos (Experto)
function searchEgresos(query) {
    query = query.toLowerCase().trim();
    let hasMatch = false;
    document.querySelectorAll('.egreso-item').forEach(item => {
        const name = item.dataset.name || '';
        const cat = item.dataset.category || '';
        const match = !query || name.includes(query) || cat.includes(query);

        if (match) {
            item.classList.remove('search-hidden');
            hasMatch = true;
        } else {
            item.classList.add('search-hidden');
        }
    });
    const emptyState = document.getElementById('egresosEmptyState');
    if (emptyState) emptyState.style.display = (!hasMatch && query) ? 'flex' : 'none';
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
            case 'deudores':
                show = card.dataset.deudor === '1';
                break;
        }
        card.style.display = show ? '' : 'none';
    });
}

// Check if a specific card should be hidden based on the active filter
function checkCardVisibility(card) {
    const activePill = document.querySelector('.filter-pill.active');
    if (!activePill) return;
    const filter = activePill.dataset.filter;
    if (filter === 'todos') return;

    let show = true;
    switch (filter) {
        case 'pendientes': show = !card.classList.contains('entregado'); break;
        case 'entregados': show = card.classList.contains('entregado'); break;
        case 'arroz': show = card.dataset.arroz === '1'; break;
        case 'pagados': show = card.dataset.pagado === '1'; break;
        case 'deudores': show = card.dataset.deudor === '1'; break;
    }

    if (!show) {
        card.classList.add('hide-smoothly');
        setTimeout(() => {
            card.style.display = 'none';
            card.classList.remove('hide-smoothly');
        }, 300);
    }
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
let pinModule = 'cuadre';
let pinData = {};
let pinCallback = null;

// Abrir caja - show PIN modal
function solicitarApertura() {
    pinModule = 'cuadre';
    pinAction = 'abrir_caja';
    pinData = {};
    pinCallback = () => location.reload();

    document.getElementById('modalPinTitle').textContent = '🔐 Abrir Jornada';
    document.getElementById('modalPinDesc').textContent = 'Ingresa tu PIN para abrir la jornada';
    document.getElementById('btnSubmitPin').textContent = '🟢 Abrir Jornada';
    document.getElementById('btnSubmitPin').className = 'btn btn-success';
    document.getElementById('modalPinExtra').style.display = 'none';

    // Clear inputs
    document.querySelectorAll('.pin-input').forEach(i => i.value = '');
    openModal('modalPin');
    setTimeout(() => {
        const firstInput = document.querySelector('.pin-input');
        if (firstInput) firstInput.focus();
    }, 300);
}

function solicitarCierre(actionType = 'cerrar_caja') {
    pinModule = 'cuadre';
    pinAction = actionType;
    pinData = {};
    pinCallback = () => location.reload();

    document.getElementById('modalPinTitle').textContent = '🔒 Cerrar Jornada';

    if (actionType === 'cerrar_caja_hard') {
        document.getElementById('modalPinDesc').innerHTML = '<span class="text-danger fw-bold">⚠️ ATENCIÓN: Se ocultarán todos los clientes y entregas para empezar desde cero.</span><br>Ingresa tu PIN:';
    } else {
        document.getElementById('modalPinDesc').textContent = 'Ingresa tu PIN para cerrar la jornada (Los clientes se mantendrán para el siguiente turno)';
    }

    document.getElementById('btnSubmitPin').textContent = '🔴 Cerrar Jornada';
    document.getElementById('btnSubmitPin').className = 'btn btn-danger';

    document.getElementById('modalPinExtra').style.display = 'block';
    document.getElementById('pinExtraInput').value = '';

    // Clear inputs
    document.querySelectorAll('.pin-input').forEach(i => i.value = '');

    // Si modalTipoCierre está abierto, cerrarlo primero
    const modalTipo = document.getElementById('modalTipoCierre');
    if (modalTipo && modalTipo.classList.contains('active')) {
        closeModal('modalTipoCierre');
    }

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

    const payload = { ...pinData, pin };
    if (pinAction === 'cerrar_caja' || pinAction === 'cerrar_caja_hard') {
        const fisico = document.getElementById('pinExtraInput').value;
        if (fisico === '') {
            showToast('Ingresa el monto de efectivo físico', 'error');
            return;
        }
        payload.efectivo_fisico = fisico;
    }

    const result = await apiCall(pinModule, pinAction, payload);

    if (result.success) {
        closeModal('modalPin');
        if (pinCallback) pinCallback(result);
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
