<?php
/**
 * Configuración de Base de Datos - SG_POLLADA
 * 
 * Prioridad de credenciales:
 * 1. config/env.php (archivo local, gitignored)
 * 2. Variables de entorno del servidor
 * 3. Valores por defecto (Laragon)
 * 
 * Compatible con: Laragon, InfinityFree, Railway
 */

// 1. Cargar manejador de errores global
require_once __DIR__ . '/error_handler.php';

// Cargar credenciales locales si existen
$env = [];
$envFile = __DIR__ . '/env.php';
if (file_exists($envFile)) {
    $env = require $envFile;
}

$db_host = $env['DB_HOST'] ?? (getenv('MYSQLHOST') ?: 'localhost');
$db_port = $env['DB_PORT'] ?? (getenv('MYSQLPORT') ?: '3306');
$db_name = $env['DB_NAME'] ?? (getenv('MYSQLDATABASE') ?: 'sg_pollada');
$db_user = $env['DB_USER'] ?? (getenv('MYSQLUSER') ?: 'root');
$db_pass = $env['DB_PASS'] ?? (getenv('MYSQLPASSWORD') ?: '');

try {
    $pdo = new PDO(
        "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    if (php_sapi_name() === 'cli') {
        die("Error de conexión: " . $e->getMessage() . "\n");
    }
    http_response_code(500);
    die('<div style="text-align:center;padding:40px;font-family:sans-serif;">
        <h2>⚠️ Error de conexión a la base de datos</h2>
        <p>Verifica que MySQL esté corriendo y la base de datos exista.</p>
        <p style="color:#999;font-size:12px;">Revisa config/env.php con tus credenciales.</p>
    </div>');
}
