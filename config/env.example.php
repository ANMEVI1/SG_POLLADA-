<?php
/**
 * Credenciales de Base de Datos - TEMPLATE
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como "env.php" en la misma carpeta
 * 2. Edita los valores con tus credenciales reales
 * 3. env.php está en .gitignore (NO se sube a git)
 * 
 * INFINITYFREE:
 * - Host: sqlXXX.infinityfree.com (ver en panel → MySQL Databases)
 * - User: if0_XXXXXXX (tu usuario de InfinityFree)
 * - Pass: la contraseña que creaste para la BD
 * - Name: if0_XXXXXXX_sgpollada (prefijo + nombre)
 * 
 * LARAGON (local):
 * - Host: localhost
 * - User: root
 * - Pass: (vacío)
 * - Name: sg_pollada
 */

return [
    'DB_HOST' => 'localhost',
    'DB_PORT' => '3306',
    'DB_NAME' => 'sg_pollada',
    'DB_USER' => 'root',
    'DB_PASS' => '',
];
