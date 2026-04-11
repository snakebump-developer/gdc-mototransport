<?php
/**
 * Migrazione: aggiorna l'ENUM degli stati preventivo.
 * - 'bozza' e 'inviato' → 'nuovo'
 * - ENUM diventa: nuovo, confermato, in_lavorazione, completato, annullato
 *
 * Eseguire UNA SOLA VOLTA su Railway dopo il deploy:
 *   https://tuodominio.up.railway.app/api/run-migrate
 *
 * Richiede: essere loggato come admin.
 */
require_once __DIR__ . '/../../src/auth.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    // 1. Aggiorna i record con vecchi stati
    $updated = $pdo->exec("UPDATE preventivi SET stato = 'nuovo' WHERE stato IN ('bozza', 'inviato')");

    // 2. Altera l'ENUM della colonna (aggiunge 'nuovo', rimuove 'bozza' e 'inviato')
    $pdo->exec("ALTER TABLE preventivi
        MODIFY COLUMN stato ENUM('nuovo','confermato','in_lavorazione','completato','annullato')
        NOT NULL DEFAULT 'nuovo'");

    // 3. Verifica risultato
    $counts = $pdo->query("
        SELECT stato, COUNT(*) AS cnt
        FROM preventivi
        GROUP BY stato
        ORDER BY stato
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success'          => true,
        'records_migrated' => $updated,
        'stati_counts'     => $counts,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore DB: ' . $e->getMessage()]);
}
