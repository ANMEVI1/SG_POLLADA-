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

    <script src="assets/js/app.js"></script>
</body>
</html>
