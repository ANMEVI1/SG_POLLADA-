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
        <span class="header-badge">SG Pollada</span>
    </header>

    <!-- Main Content -->
    <main class="main-content">
