<?php
require_once __DIR__ . '/config/database.php';

try {
    // 3. Add quiere_arroz
    $pdo->exec("ALTER TABLE cliente ADD COLUMN quiere_arroz TINYINT(1) DEFAULT 0");
    echo "Columna 'quiere_arroz' anadida correctamente.<br>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "La columna 'quiere_arroz' ya existe.<br>";
    } else {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}
echo "<br><b>Migracion completada!</b>";
?>
