<?php
require_once __DIR__ . '/db.php';

function createOrder($userId, $totale, $metodoPagamento, $items = [], $note = '')
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Crea l'ordine
        $stmt = $pdo->prepare("INSERT INTO ordini (user_id, totale, metodo_pagamento, note) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $totale, $metodoPagamento, $note]);
        $ordineId = $pdo->lastInsertId();

        // Aggiungi i dettagli dell'ordine
        if (!empty($items)) {
            $stmt = $pdo->prepare("INSERT INTO ordini_dettagli (ordine_id, descrizione, quantita, prezzo_unitario) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([
                    $ordineId,
                    $item['descrizione'],
                    $item['quantita'] ?? 1,
                    $item['prezzo_unitario']
                ]);
            }
        }

        $pdo->commit();
        return $ordineId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getUserOrders($userId, $limit = 10, $offset = 0)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM ordini 
        WHERE user_id = ? 
        ORDER BY creato_il DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$userId, $limit, $offset]);
    return $stmt->fetchAll();
}

function getAllOrders($limit = 50, $offset = 0, $filters = [])
{
    global $pdo;

    $where = [];
    $params = [];

    if (isset($filters['stato'])) {
        $where[] = "stato = ?";
        $params[] = $filters['stato'];
    }

    if (isset($filters['user_id'])) {
        $where[] = "user_id = ?";
        $params[] = $filters['user_id'];
    }

    $sql = "SELECT o.*, u.username, u.email 
            FROM ordini o 
            LEFT JOIN utenti u ON o.user_id = u.id";

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " ORDER BY o.creato_il DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getOrderById($ordineId)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT o.*, u.username, u.email 
        FROM ordini o 
        LEFT JOIN utenti u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$ordineId]);
    return $stmt->fetch();
}

function getOrderDetails($ordineId)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM ordini_dettagli WHERE ordine_id = ?");
    $stmt->execute([$ordineId]);
    return $stmt->fetchAll();
}

function updateOrderStatus($ordineId, $stato, $transactionId = null)
{
    global $pdo;

    $sql = "UPDATE ordini SET stato = ?, aggiornato_il = CURRENT_TIMESTAMP";
    $params = [$stato];

    if ($transactionId !== null) {
        $sql .= ", transaction_id = ?";
        $params[] = $transactionId;
    }

    $sql .= " WHERE id = ?";
    $params[] = $ordineId;

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function getOrderStats()
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as totale_ordini,
            COALESCE(SUM(CASE WHEN stato = 'completed' THEN totale ELSE 0 END), 0) as totale_vendite,
            COUNT(CASE WHEN stato = 'pending' THEN 1 END) as ordini_pending,
            COUNT(CASE WHEN stato = 'processing' THEN 1 END) as ordini_processing,
            COUNT(CASE WHEN stato = 'completed' THEN 1 END) as ordini_completati,
            COUNT(CASE WHEN stato = 'cancelled' THEN 1 END) as ordini_cancellati
        FROM ordini
    ");

    return $stmt->fetch();
}

function getAllPreventivi($limit = 100, $offset = 0)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, u.username
        FROM preventivi p
        LEFT JOIN utenti u ON p.user_id = u.id
        ORDER BY p.data_ritiro ASC, p.creato_il DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Restituisce un array [ 'YYYY-MM-DD' => count ] per il calendario admin.
 */
function getPreventiviCountByDate(): array
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT data_ritiro, COUNT(*) AS cnt
        FROM preventivi
        WHERE data_ritiro IS NOT NULL AND data_ritiro != ''
        GROUP BY data_ritiro
    ");
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[$row['data_ritiro']] = (int) $row['cnt'];
    }
    return $result;
}

function getPreventivoById($id)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.email
        FROM preventivi p
        LEFT JOIN utenti u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updatePreventivoStato($id, $stato)
{
    global $pdo;
    $stati = ['bozza', 'inviato', 'confermato', 'in_lavorazione', 'completato', 'annullato'];
    if (!in_array($stato, $stati)) {
        throw new Exception("Stato preventivo non valido");
    }
    $stmt = $pdo->prepare("UPDATE preventivi SET stato = ?, aggiornato_il = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$stato, $id]);
}

function getUserPreventivi($userId, $limit = 50)
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*,
               pg.stato           AS pagamento_stato_pg,
               pg.importo         AS pagamento_importo,
               pg.stripe_brand    AS pagamento_brand,
               pg.stripe_ultimi_4 AS pagamento_ultimi4,
               pg.stripe_receipt_url AS pagamento_receipt
        FROM preventivi p
        LEFT JOIN pagamenti pg ON pg.preventivo_id = p.id
        WHERE p.user_id = ?
        ORDER BY p.creato_il DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}
