<?php
/**
 * Simple SQLite Migration Runner
 */

require_once __DIR__ . '/../config/database.php';

echo "--- Iniciando Migrations (SQLite) ---\n";

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("Falha ao conectar ao banco de dados.\n");
}

// Ensure foreign keys are enabled in SQLite
$conn->exec("PRAGMA foreign_keys = ON;");

$migrationsDir = __DIR__ . '/migrations';
$files = scandir($migrationsDir);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        echo "Executando: $file... ";
        
        $sql = file_get_contents($migrationsDir . '/' . $file);
        
        try {
            $conn->exec($sql);
            echo "OK\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "OK (Já existe)\n";
            } else {
                echo "ERRO: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "--- Migrations Concluídas ---\n";
?>
