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
    // 0. Disabilita strict mode di sessione: evita che i warning MySQL
    //    vengano trattati come eccezioni da PDO durante l'ALTER TABLE
    $pdo->exec("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION'");

    // 1. Normalizza 'bozza' e 'inviato' → 'nuovo'
    $updated = $pdo->exec("UPDATE preventivi SET stato = 'nuovo' WHERE stato IN ('bozza', 'inviato')");

    // 1b. Sicurezza: forza a 'nuovo' qualsiasi valore fuori dall'ENUM target
    //     (cattura NULL, stringhe vuote, stati obsoleti non previsti)
    $pdo->exec("UPDATE preventivi
        SET stato = 'nuovo'
        WHERE stato IS NULL
           OR stato NOT IN ('nuovo','confermato','in_lavorazione','completato','annullato')");

    // 2. Altera l'ENUM della colonna
    $pdo->exec("ALTER TABLE preventivi
        MODIFY COLUMN stato ENUM('nuovo','confermato','in_lavorazione','completato','annullato')
        NOT NULL DEFAULT 'nuovo'");

    // 3. Aggiunge colonna stripe_refund_id se non esiste (compatibile MySQL 5.7)
    $colExists = $pdo->query("
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME  = 'pagamenti'
          AND COLUMN_NAME = 'stripe_refund_id'
    ")->fetchColumn();
    if (!$colExists) {
        $pdo->exec("ALTER TABLE pagamenti
            ADD COLUMN stripe_refund_id VARCHAR(255) NULL AFTER stripe_receipt_url");
    }

    // 4. Verifica risultato
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
