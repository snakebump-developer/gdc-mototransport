<?php

/**
 * API: Verifica e conferma un pagamento Stripe già eseguito.
 *
 * Questo endpoint viene chiamato:
 *   1. Da quote-modal.js dopo il pagamento in-modal (flusso senza redirect)
 *   2. Da payment-success.php dopo il redirect Stripe (flusso 3DS/redirect)
 *
 * Recupera il PaymentIntent direttamente da Stripe API e aggiorna il DB
 * solo se lo stato è 'succeeded'. Funziona sia in locale che in produzione,
 * ed è il fallback quando il webhook non è ancora stato recapitato.
 *
 * Parametri POST JSON:
 *   - payment_intent_id  (string, obbligatorio)
 *   - preventivo_id      (int, obbligatorio)
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$paymentIntentId = trim($data['payment_intent_id'] ?? '');
$preventivoId    = (int) ($data['preventivo_id'] ?? 0);

if (empty($paymentIntentId) || $preventivoId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Parametri mancanti']);
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/payments/stripe.php';
require_once __DIR__ . '/../../src/db.php';

try {
    // Recupera il PaymentIntent da Stripe (fonte di verità)
    $intent = retrievePaymentIntent($paymentIntentId);

    if ($intent === null) {
        http_response_code(502);
        echo json_encode(['error' => 'Impossibile recuperare il pagamento da Stripe']);
        exit;
    }

    if ($intent->status !== 'succeeded') {
        echo json_encode([
            'success' => false,
            'status'  => $intent->status,
            'message' => 'Il pagamento non risulta completato su Stripe (stato: ' . $intent->status . ')',
        ]);
        exit;
    }

    // Estrai dati carta dalla prima charge
    $brand      = null;
    $ultimi4    = null;
    $metodo     = null;
    $receiptUrl = null;

    $charges = $intent->charges->data ?? [];
    if (!empty($charges)) {
        $charge     = $charges[0];
        $receiptUrl = $charge->receipt_url ?? null;
        if (!empty($charge->payment_method_details->card)) {
            $card    = $charge->payment_method_details->card;
            $brand   = $card->brand ?? null;
            $ultimi4 = $card->last4 ?? null;
            $metodo  = 'card';
        }
    }

    // Aggiorna tabella pagamenti (idempotente: usa UPDATE, non INSERT)
    $stmtPag = $pdo->prepare("
        UPDATE pagamenti SET
            stato               = 'paid',
            stripe_metodo       = ?,
            stripe_ultimi_4     = ?,
            stripe_brand        = ?,
            stripe_receipt_url  = ?,
            aggiornato_il       = CURRENT_TIMESTAMP
        WHERE stripe_payment_intent_id = ?
          AND stato != 'paid'
    ");
    $stmtPag->execute([$metodo, $ultimi4, $brand, $receiptUrl, $paymentIntentId]);

    // Aggiorna preventivo
    $stmtPrev = $pdo->prepare("
        UPDATE preventivi SET
            pagamento_stato = 'pagato',
            stato           = 'confermato',
            aggiornato_il   = CURRENT_TIMESTAMP
        WHERE id = ?
          AND pagamento_stato != 'pagato'
    ");
    $stmtPrev->execute([$preventivoId]);

    echo json_encode([
        'success'     => true,
        'receipt_url' => $receiptUrl,
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(502);
    echo json_encode(['error' => 'Errore Stripe: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore interno']);
}
