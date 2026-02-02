<?php
$config = require __DIR__ . '/config.php';
$dbPath = $config['db_dir'] . '/' . $config['db_name'];

try {
    // Verifica che la directory del database esista
    if (!is_dir($config['db_dir'])) {
        die("Errore: Directory database non trovata. Esegui 'php src/setup.php' per inizializzare il database.");
    }
    
    // Verifica che il file del database esista
    if (!file_exists($dbPath)) {
        die("Errore: Database non trovato. Esegui 'php src/setup.php' per inizializzare il database.");
    }
    
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore critico del database: " . htmlspecialchars($e->getMessage()));
}