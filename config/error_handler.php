<?php
/**
 * Manejador Global de Errores - SG_POLLADA
 * Captura y registra errores sin romper silenciosamente.
 */

// Asegurar que el directorio de logs exista
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}
$logFile = $logDir . '/error.log';

// Habilitar reporte de errores en PHP
error_reporting(E_ALL);

// Configurar cómo se escriben los errores
function log_custom_error($level, $message, $file = '', $line = 0) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    $levelName = "ERROR ($level)";
    
    switch ($level) {
        case E_ERROR: case E_USER_ERROR: $levelName = 'FATAL'; break;
        case E_WARNING: case E_USER_WARNING: $levelName = 'WARNING'; break;
        case E_NOTICE: case E_USER_NOTICE: $levelName = 'NOTICE'; break;
    }
    
    // Ignorar warnings irrelevantes de librerías de terceros (si hubiera) o strict standards
    if ($level === E_STRICT || $level === E_DEPRECATED) {
        return false;
    }

    $logEntry = "[$date] [$levelName] $message en $file (línea $line)" . PHP_EOL;
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Dejar que PHP siga su curso normal (no interrumpimos la web a menos que sea fatal)
    return false;
}

// 1. Manejar Warnings y Notices
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    log_custom_error($errno, $errstr, $errfile, $errline);
});

// 2. Manejar Excepciones no atrapadas (ej: PDOExceptions no capturadas)
set_exception_handler(function($exception) {
    log_custom_error(E_ERROR, "Excepción no atrapada: " . $exception->getMessage(), $exception->getFile(), $exception->getLine());
});

// 3. Manejar Errores Fatales (ej: Sintaxis, Memoria excedida)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        log_custom_error($error['type'], "Error Fatal: " . $error['message'], $error['file'], $error['line']);
    }
});
