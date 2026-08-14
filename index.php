<?php
/**
 * Router Principal - SG_POLLADA
 * Punto de entrada de la aplicación
 */

require_once __DIR__ . '/config/database.php';

// Módulo y vista solicitada
$page = $_GET['page'] ?? 'entrega';
$action = $_POST['action'] ?? $_GET['action'] ?? null;

// Whitelist de módulos permitidos
$allowed_pages = ['inversion', 'personal', 'entrega', 'cuadre'];

if (!in_array($page, $allowed_pages)) {
    $page = 'entrega';
}

// Si es petición API (AJAX), delegar al controlador API
if ($action && isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/api/' . $page . '.php';
    exit;
}

// Renderizar página completa
include __DIR__ . '/views/layout/header.php';
include __DIR__ . '/views/' . $page . '.php';
include __DIR__ . '/views/layout/footer.php';
