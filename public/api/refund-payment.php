<?php

/**
 * API: Rimborso di un preventivo già pagato e impostazione stato "annullato".
 *
 * Metodo: POST
 * Parametri JSON (body):
 *   - preventivo_id  (int, obbligatorio)
 *   - motivo         (string, opzionale: requested_by_customer|duplicate|fraudulent)
 *
 * Richiede sessione admin attiva.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../src/payments/stripe.php';

// Solo admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accesso negato']);
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

$body        = json_decode(file_get_contents('php://input'), true) ?? [];
$preventivoId = (int)($body['preventivo_id'] ?? 0);
$motivo       = $body['motivo'] ?? 'requested_by_customer';
$skipRefund   = !empty($body['skip_refund']);

$motiviValidi = ['requested_by_customer', 'duplicate', 'fraudulent'];
if (!in_array($motivo, $motiviValidi, true)) {
    $motivo = 'requested_by_customer';
}

if ($preventivoId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'ID preventivo non valido']);
    exit;
}

try {
    // Recupera dati pagamento del preventivo
    $stmt = $pdo->prepare("
        SELECT pg.stripe_payment_intent_id, pg.importo, pg.stato AS pg_stato,
               p.stato AS prev_stato, p.pagamento_stato
        FROM preventivi p
        LEFT JOIN pagamenti pg ON pg.preventivo_id = p.id
        WHERE p.id = ?
        LIMIT 1
    ");
    $stmt->execute([$preventivoId]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Preventivo non trovato']);
        exit;
    }

    $paymentIntentId = $row['stripe_payment_intent_id'] ?? null;
    $pagamentoStato  = $row['pg_stato'] ?? null;
    $prevStato       = $row['prev_stato'] ?? null;

    // Se già annullato, uscita anticipata
    if ($prevStato === 'annullato') {
        echo json_encode(['success' => true, 'message' => 'Preventivo già annullato', 'rimborso_eseguito' => false]);
        exit;
    }

    $rimborsoEseguito = false;

    // Esegui rimborso Stripe solo se esiste un PaymentIntent pagato E non viene saltato
    if (!$skipRefund && !empty($paymentIntentId) && in_array($pagamentoStato, ['paid', 'pagato'], true)) {
        $refund = processRefund($paymentIntentId, null, $motivo);

        // Aggiorna tabella pagamenti
        $pdo->prepare("
            UPDATE pagamenti
            SET stato = 'refunded',
                stripe_refund_id = ?,
                aggiornato_il = CURRENT_TIMESTAMP
            WHERE stripe_payment_intent_id = ?
        ")->execute([$refund->id, $paymentIntentId]);

        $rimborsoEseguito = true;
    }

    // Imposta preventivo come annullato
    $pdo->prepare("
        UPDATE preventivi
        SET stato = 'annullato',
            pagamento_stato = CASE WHEN ? = 1 THEN 'rimborsato' ELSE pagamento_stato END,
            aggiornato_il = CURRENT_TIMESTAMP
        WHERE id = ?
    ")->execute([$rimborsoEseguito ? 1 : 0, $preventivoId]);

    echo json_encode([
        'success'          => true,
        'rimborso_eseguito' => $rimborsoEseguito,
        'message'          => $rimborsoEseguito
            ? 'Preventivo annullato e rimborso avviato su Stripe.'
            : 'Preventivo annullato (nessun pagamento da rimborsare).',
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => 'Errore Stripe: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno: ' . $e->getMessage()]);
}
