<?php

/**
 * API: attiva/disattiva la modalità manutenzione.
 * Solo admin autenticati possono chiamarla.
 */
require_once __DIR__ . '/../../src/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    // Assicura che la tabella esista (sicuro nei deploy in cui setup non è stato rieseguito)
    $pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
        setting_key   VARCHAR(100) PRIMARY KEY,
        setting_value VARCHAR(500) NOT NULL DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES ('maintenance_mode', '0')");

    // Legge valore attuale
    $current = (string)$pdo->query("SELECT setting_value FROM app_settings WHERE setting_key = 'maintenance_mode' LIMIT 1")->fetchColumn();

    // Inverti
    $newValue = ($current === '1') ? '0' : '1';

    $stmt = $pdo->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
    $stmt->execute([$newValue]);

    echo json_encode([
        'success'          => true,
        'maintenance_mode' => $newValue === '1',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore DB: ' . $e->getMessage()]);
}
