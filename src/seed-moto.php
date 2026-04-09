<?php

/**
 * Seed del catalogo moto da database/catalogo-moto.json
 * Eseguire una sola volta (è idempotente grazie a INSERT OR IGNORE).
 *
 * Uso: php src/seed-moto.php
 */

$config  = require __DIR__ . '/config.php';
$dbPath  = $config['db_dir'] . '/' . $config['db_name'];
$jsonPath = __DIR__ . '/../database/catalogo-moto.json';

if (!file_exists($jsonPath)) {
    die("File JSON non trovato: $jsonPath\n");
}

$json = json_decode(file_get_contents($jsonPath), true);
if (!is_array($json)) {
    die("Errore parsing JSON: " . json_last_error_msg() . "\n");
}

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("PRAGMA journal_mode = WAL");

    $stmt = $pdo->prepare("INSERT OR IGNORE INTO catalogo_moto (marca, modello) VALUES (?, ?)");

    $totalInserted = 0;
    $pdo->beginTransaction();

    foreach ($json as $entry) {
        $marca = trim($entry['marca'] ?? '');
        if (empty($marca) || !isset($entry['moto']) || !is_array($entry['moto'])) continue;

        foreach ($entry['moto'] as $modello) {
            $modello = trim($modello);
            if (empty($modello)) continue;
            $stmt->execute([$marca, $modello]);
            if ($stmt->rowCount() > 0) $totalInserted++;
        }
    }

    $pdo->commit();

    $total = (int)$pdo->query("SELECT COUNT(*) FROM catalogo_moto")->fetchColumn();
    echo "Seed completato!\n";
    echo "  Nuovi record inseriti : $totalInserted\n";
    echo "  Totale nel catalogo   : $total\n";
} catch (PDOException $e) {
    die("Errore DB: " . $e->getMessage() . "\n");
}
