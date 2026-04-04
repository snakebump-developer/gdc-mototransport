<?php

/**
 * Webhook Stripe - gestisce gli eventi asincroni di pagamento.
 *
 * Registra questo URL nel dashboard Stripe:
 *   https://dashboard.stripe.com/webhooks
 *   URL: https://tuodominio.it/api/webhook-stripe.php
 *   Evento da abilitare: payment_intent.succeeded, payment_intent.payment_failed
 *
 * Dopo la creazione del webhook, copia il Signing Secret in .env:
 *   STRIPE_WEBHOOK_SECRET=whsec_...
 */

// Nessun output HTML prima della risposta
header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../../src/config.php';
$webhookSecret = $config['stripe']['webhook_secret'];

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Se il webhook secret è configurato, verifica la firma
if (!empty($webhookSecret)) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../src/payments/stripe.php';

    $event = verifyStripeWebhook($payload, $sigHeader, $webhookSecret);
    if ($event === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Firma webhook non valida']);
        exit;
    }
} else {
    // In sviluppo senza webhook secret: decodifica direttamente
    require_once __DIR__ . '/../../vendor/autoload.php';
    $event = \Stripe\Event::constructFrom(json_decode($payload, true));
}

require_once __DIR__ . '/../../src/db.php';

try {
    switch ($event->type) {

        case 'payment_intent.succeeded':
            $intent       = $event->data->object;
            $intentId     = $intent->id;
            $preventivoId = (int) ($intent->metadata['preventivo_id'] ?? 0);

            // Recupera info metodo di pagamento (carta)
            $brand    = null;
            $ultimi4  = null;
            $metodo   = null;
            $receiptUrl = null;

            if (!empty($intent->charges->data)) {
                $charge     = $intent->charges->data[0];
                $receiptUrl = $charge->receipt_url ?? null;
                if (!empty($charge->payment_method_details->card)) {
                    $card    = $charge->payment_method_details->card;
                    $brand   = $card->brand ?? null;
                    $ultimi4 = $card->last4 ?? null;
                    $metodo  = 'card';
                }
            }

            // Aggiorna tabella pagamenti
            $pdo->prepare("
                UPDATE pagamenti SET
                    stato = 'paid',
                    stripe_metodo = ?,
                    stripe_ultimi_4 = ?,
                    stripe_brand = ?,
                    stripe_receipt_url = ?,
                    aggiornato_il = CURRENT_TIMESTAMP
                WHERE stripe_payment_intent_id = ?
            ")->execute([$metodo, $ultimi4, $brand, $receiptUrl, $intentId]);

            // Aggiorna preventivo
            if ($preventivoId > 0) {
                $pdo->prepare("
                    UPDATE preventivi SET
                        pagamento_stato = 'pagato',
                        stato = 'confermato',
                        aggiornato_il = CURRENT_TIMESTAMP
                    WHERE id = ?
                ")->execute([$preventivoId]);
            }
            break;

        case 'payment_intent.payment_failed':
            $intent   = $event->data->object;
            $intentId = $intent->id;
            $preventivoId = (int) ($intent->metadata['preventivo_id'] ?? 0);

            $pdo->prepare("
                UPDATE pagamenti SET stato = 'failed', aggiornato_il = CURRENT_TIMESTAMP
                WHERE stripe_payment_intent_id = ?
            ")->execute([$intentId]);

            if ($preventivoId > 0) {
                $pdo->prepare("
                    UPDATE preventivi SET pagamento_stato = 'fallito', aggiornato_il = CURRENT_TIMESTAMP
                    WHERE id = ?
                ")->execute([$preventivoId]);
            }
            break;

        case 'charge.refunded':
            $charge   = $event->data->object;
            $intentId = $charge->payment_intent ?? null;
            if ($intentId) {
                $pdo->prepare("
                    UPDATE pagamenti SET stato = 'refunded', aggiornato_il = CURRENT_TIMESTAMP
                    WHERE stripe_payment_intent_id = ?
                ")->execute([$intentId]);
                // Aggiorna preventivo via join
                $stmt = $pdo->prepare("
                    SELECT preventivo_id FROM pagamenti WHERE stripe_payment_intent_id = ?
                ");
                $stmt->execute([$intentId]);
                $row = $stmt->fetch();
                if ($row) {
                    $pdo->prepare("
                        UPDATE preventivi SET pagamento_stato = 'rimborsato', aggiornato_il = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ")->execute([$row['preventivo_id']]);
                }
            }
            break;
    }

    http_response_code(200);
    echo json_encode(['received' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel processare il webhook']);
}
