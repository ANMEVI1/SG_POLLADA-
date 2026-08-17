<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#E65100">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>SG Pollada - Sistema de Gestión</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Header -->
    <header class="app-header">
        <h1>
            <?php
            $titles = [
                'inversion' => ['icon' => '💰', 'text' => 'Inversión'],
                'personal'  => ['icon' => '👥', 'text' => 'Personal'],
                'entrega'   => ['icon' => '🍗', 'text' => 'Entregas'],
                'cuadre'    => ['icon' => '📊', 'text' => 'Cuadre'],
            ];
            $t = $titles[$page] ?? $titles['entrega'];
            echo '<span class="icon">' . $t['icon'] . '</span> ' . $t['text'];
            ?>
        </h1>
        <div class="header-actions" style="display:flex; align-items:center; gap:10px;">
              <button class="btn-icon" onclick="openModal('modalAyuda')" style="color:var(--text); background:rgba(255,255,255,0.2); border-radius:50%; width:36px; height:36px; display:flex; justify-content:center; align-items:center; border:none; cursor:pointer; font-weight:bold; font-size:18px;" title="Ayuda / Tutorial">
                  ?
              </button>
              <button class="btn-icon" onclick="openModal('modalQR')" style="color:var(--text); background:rgba(255,255,255,0.2); border-radius:50%; width:36px; height:36px; display:flex; justify-content:center; align-items:center; border:none; cursor:pointer;" title="Mostrar QR">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </button>
            <span class="header-badge" style="margin-left:0;">SG Pollada V-0.0.0.8</span>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
