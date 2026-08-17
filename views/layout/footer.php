    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="?page=inversion" class="nav-item <?= $page === 'inversion' ? 'active' : '' ?>">
            <span class="nav-icon">💰</span>
            <span class="nav-label">Inversión</span>
        </a>
        <a href="?page=personal" class="nav-item <?= $page === 'personal' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span>
            <span class="nav-label">Personal</span>
        </a>
        <a href="?page=entrega" class="nav-item <?= $page === 'entrega' ? 'active' : '' ?>">
            <span class="nav-icon">🍗</span>
            <span class="nav-label">Entregas</span>
        </a>
        <a href="?page=cuadre" class="nav-item <?= $page === 'cuadre' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Cuadre</span>
        </a>
    </nav>

    <!-- MODAL: AYUDA / TUTORIAL -->
    <div class="modal-overlay" id="modalAyuda">
        <div class="modal" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header">
                <span class="modal-title" style="color: var(--primary);">📖 Manual del Sistema</span>
                <button class="modal-close" onclick="closeModal('modalAyuda')">✕</button>
            </div>
            <div class="modal-body" style="overflow-y: auto; text-align: left; background: #f8f9fa; padding: 16px;">
                
                <h4 style="color:var(--primary); margin-top:0; font-size:15px;">🚀 1. Abrir y Cerrar el Día</h4>
                <div class="card" style="margin-bottom:16px; padding:12px; font-size:13px; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <p style="margin-top:0;">Para empezar a trabajar, ve a la pestaña <b>Cuadre</b> y abre la caja. Al terminar, usa el botón "Cierre de Jornada":</p>
                    <ul style="margin:8px 0; padding-left:20px; line-height:1.5;">
                        <li><b>🔒 Cierre Normal:</b> Limpia las ventas de hoy, pero guarda a tus clientes para venderles mañana sin volver a escribirlos.</li>
                        <li><b>🗑️ Hard Reset:</b> Borra TODO. Úsalo cuando la actividad termine por completo y quieras empezar otra pollada desde cero el otro mes.</li>
                    </ul>
                </div>

                <h4 style="color:var(--primary); font-size:15px;">🍗 2. Despacho y Entregas</h4>
                <div class="card" style="margin-bottom:16px; padding:12px; font-size:13px; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <p style="margin-top:0;">En la pestaña <b>Entregas</b> ves quién falta recoger su pedido. Usa los filtros rápidos de arriba (Pendientes, Con Arroz) para agilizar el despacho.</p>
                    
                    <b style="color:#d32f2f;">Los Botones Mágicos:</b>
                    <ul style="margin:8px 0; padding-left:20px; line-height:1.5;">
                        <li><b>📦 Entregar:</b> Tócalo al dar la comida. Se pondrá Verde.</li>
                        <li><b>🍚 Arroz:</b> Tócalo si el cliente pidió su porción con arroz en lugar de papas.</li>
                        <li><b>💵 Pagar:</b> Registra cómo te pagaron:
                            <br>👉 <i>1er toque:</i> Cambia a <b>Efectivo</b>.
                            <br>👉 <i>2do toque:</i> Cambia a <b>Yape/Lemon</b> y abre un cuadro con tu código QR en la pantalla para que el cliente lo escanee y pague.
                            <br>👉 <i>3er toque:</i> Si te equivocaste y quieres regresarlo a "Debe dinero", te pedirá la contraseña (PIN) por seguridad.
                        </li>
                    </ul>
                </div>

                <h4 style="color:var(--primary); font-size:15px;">🎟️ 3. Tickets por WhatsApp</h4>
                <div class="card" style="margin-bottom:16px; padding:12px; font-size:13px; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <p style="margin-top:0; margin-bottom:0;">En la pestaña <b>Entregas</b> toca la sub-pestaña <b>👤 Clientes</b>. Verás un botón de diálogo verde (💬). Tócalo, pon el número de celular del cliente y envíale automáticamente su comprobante digital en PDF. No tienes que guardar el número en tus contactos.</p>
                </div>

                <h4 style="color:var(--primary); font-size:15px;">💰 4. Registro de Gastos</h4>
                <div class="card" style="margin-bottom:16px; padding:12px; font-size:13px; background:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                    <p style="margin-top:0;">Todo dinero que sale de caja se anota en <b>Inversión</b>:</p>
                    <ul style="margin:8px 0 0 0; padding-left:20px; line-height:1.5;">
                        <li><b>Productos:</b> Cosas grandes (Pollo, Papas, Aceite).</li>
                        <li><b>Imprevistos:</b> Dinero rápido de último minuto (Taxi, Hielo, etc).</li>
                    </ul>
                </div>

                <p style="text-align:center; color:#888; font-size:12px; margin-top:20px;">
                    Todo lo que hagas aquí aparecerá mágicamente sumado en tu <b>PDF del Cuadre Final</b>. ¡Tú tienes el control!
                </p>
            </div>
            <div class="modal-footer" style="padding:16px;">
                <button type="button" class="btn btn-primary" onclick="closeModal('modalAyuda')" style="width:100%; border-radius:12px; padding:12px;">¡Entendido, a trabajar!</button>
            </div>
        </div>
    </div>

    <!-- MODAL: TIPO DE CIERRE -->
    <div class="modal-overlay" id="modalTipoCierre">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">🔴 Opciones de Cierre</span>
                <button class="modal-close" onclick="closeModal('modalTipoCierre')">✕</button>
            </div>
            <div class="modal-body text-center">
                <p class="text-muted" style="font-size:14px; margin-bottom:20px;">
                    ¿Qué tipo de cierre deseas realizar?
                </p>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <button class="btn btn-primary" onclick="solicitarCierre('cerrar_caja')" style="width:100%; justify-content:center;">
                        <div>
                            <div style="font-weight:bold; font-size:15px;">🔒 Cerrar Normal</div>
                            <div style="font-size:11px; font-weight:normal;">Limpia Entregas, Conserva Clientes/Productos</div>
                        </div>
                    </button>
                    <button class="btn btn-danger btn-outline" onclick="solicitarCierre('cerrar_caja_hard')" style="width:100%; justify-content:center;">
                        <div>
                            <div style="font-weight:bold; font-size:15px;">🗑️ Cerrar y Limpiar Todo (Reset)</div>
                            <div style="font-size:11px; font-weight:normal;">Oculta absolutamente TODO. Empezar de cero.</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: WHATSAPP TICKET -->
    <div class="modal-overlay" id="modalWhatsAppTicket">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title" style="color: #25D366;">💬 Enviar Ticket Digital</span>
                <button class="modal-close" onclick="closeModal('modalWhatsAppTicket')">✕</button>
            </div>
            <div class="modal-body">
                <p class="text-muted fs-sm mb-16">
                    Se abrirá WhatsApp para enviar el ticket <strong id="waTicketCode"></strong> a <strong id="waTicketName"></strong>.
                </p>
                <div class="form-group">
                    <label class="form-label">Número de Teléfono (WhatsApp)</label>
                    <div style="display:flex; gap:8px;">
                        <input type="text" class="form-control" value="+51" disabled style="width: 60px; text-align: center; background: #eee;">
                        <input type="number" id="waPhoneNumber" class="form-control" placeholder="987654321" style="flex: 1;">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="flex-wrap: wrap; gap: 10px;">
                <button type="button" class="btn btn-primary" id="btnDescargarTicket" style="flex: 1;" onclick="descargarTicketPDF(this)">⬇️ 1. Descargar Ticket</button>
                <button type="button" class="btn btn-primary" id="btnEnviarWA" style="background: #25D366; border-color: #25D366; flex: 1; opacity: 0.5;" disabled onclick="abrirChatWhatsApp(this)">💬 2. Abrir WhatsApp</button>
                <button type="button" class="btn btn-outline" style="width: 100%;" onclick="closeModal('modalWhatsAppTicket')">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- MODAL: PIN -->
    <div class="modal-overlay" id="modalPin">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title" id="modalPinTitle">🔐 Confirmación por PIN</span>
                <button class="modal-close" onclick="closeModal('modalPin')">✕</button>
            </div>
            <div class="modal-body text-center">
                <div id="modalPinExtra" style="display:none; margin-bottom:16px;">
                    <label class="form-label" style="text-align:left">Efectivo Físico Contado (S/)</label>
                    <input type="number" id="pinExtraInput" class="form-control" step="0.10" placeholder="0.00" style="font-size:20px;text-align:center;">
                    <div class="fs-sm text-muted mt-4">¿Cuántos billetes y monedas hay en total en la caja?</div>
                </div>
                
                <p class="text-muted mb-16" style="font-size:13px" id="modalPinDesc">Ingresa el PIN de 4 dígitos</p>
                <div class="pin-input-group">
                    <input type="password" class="pin-input" maxlength="1" inputmode="numeric" pattern="\d">
                    <input type="password" class="pin-input" maxlength="1" inputmode="numeric" pattern="\d">
                    <input type="password" class="pin-input" maxlength="1" inputmode="numeric" pattern="\d">
                    <input type="password" class="pin-input" maxlength="1" inputmode="numeric" pattern="\d">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalPin')">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnSubmitPin" onclick="submitPin()">Confirmar</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalQR" style="z-index: 9999; background: rgba(0,0,0,0.9);">
        <div class="modal" style="background: transparent; box-shadow: none; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; max-height: 100vh; border-radius: 0; padding: 20px;">
            <div style="background: white; padding: 20px; border-radius: 20px; display: flex; flex-direction: column; align-items: center; max-width: 90vw;">
                <h3 style="color: #333; margin: 0 0 15px 0; font-size: 18px; text-align: center;">Escanear para acceder</h3>
                <img src="assets/qr/qr-pollada.png" alt="QR Code" style="width: 250px; height: 250px; max-width: 100%; object-fit: contain; border-radius: 10px;">
                <p style="color: #666; margin: 15px 0 0 0; font-size: 14px; text-align: center;">Apunta con la cámara de tu celular</p>
            </div>
            <button onclick="closeModal('modalQR')" style="margin-top: 30px; background: rgba(255,255,255,0.2); border: 2px solid white; color: white; width: 50px; height: 50px; border-radius: 50%; font-size: 24px; display: flex; justify-content: center; align-items: center; cursor: pointer;">✕</button>
        </div>
    </div>

    <!-- Modal Pago Yape/Lemon -->
    <div class="modal-overlay" id="modalPagoQR" style="z-index: 9999; background: rgba(0,0,0,0.9);">
        <div class="modal" style="background: transparent; box-shadow: none; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; max-height: 100vh; border-radius: 0; padding: 20px; position: relative;">
            
            <button onclick="closeModal('modalPagoQR')" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.2); border: 2px solid white; color: white; width: 40px; height: 40px; border-radius: 50%; font-size: 20px; display: flex; justify-content: center; align-items: center; cursor: pointer;">✕</button>

            <div style="background: white; padding: 20px; border-radius: 20px; display: flex; flex-direction: column; align-items: center; width: 300px; max-width: 90vw; position: relative;">
                
                <h3 style="color: #333; margin: 0 0 5px 0; font-size: 18px; text-align: center; text-transform: uppercase;">Carlo Andre Mestanza</h3>
                <p style="color: var(--primary); margin: 0 0 15px 0; font-size: 24px; font-weight: 800; text-align: center; letter-spacing: 1px;">937 660 922</p>
                
                <!-- Carousel Container -->
                <div style="position: relative; width: 100%; display: flex; justify-content: center; align-items: center;">
                    
                    <button onclick="prevQR()" style="position: absolute; left: -20px; background: var(--primary); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 10; font-size: 18px;">◄</button>
                    
                    <img id="pagoQRImg" src="assets/qr/pagos_qr/metodo_yape.jpeg" alt="QR Code Pago" style="width: 250px; height: 250px; max-width: 100%; object-fit: contain; border-radius: 10px; transition: opacity 0.3s ease-in-out;">
                    
                    <button onclick="nextQR()" style="position: absolute; right: -20px; background: var(--primary); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 10; font-size: 18px;">►</button>
                </div>

                <p id="pagoQRLabel" style="color: #666; margin: 15px 0 20px 0; font-size: 15px; text-align: center; font-weight: 600;">Yape / Plin</p>
                
                <button class="btn btn-primary" onclick="confirmarPagoQR()" style="width: 100%; font-size: 16px; padding: 14px; border-radius: 12px; box-shadow: 0 4px 10px rgba(230, 81, 0, 0.3); justify-content: center;">✅ Pago realizado</button>

            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=<?= time() ?>"></script>
</body>
</html>
