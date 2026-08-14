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

    <script src="assets/js/app.js"></script>
</body>
</html>
