<?php
require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/orders.php';

$sessionId       = $_GET['session_id']       ?? null;
$paymentId       = $_GET['PayerID']           ?? null;
$paymentIntentId = $_GET['payment_intent']    ?? null;
$redirectStatus  = $_GET['redirect_status']   ?? null;
$preventivoId    = (int) ($_GET['preventivo_id'] ?? 0);

$success = false;
$pageTitle = 'Pagamento Completato - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/auth.css'];

// Flusso Stripe Payment Element con redirect (es. 3DS)
if ($paymentIntentId && $redirectStatus === 'succeeded' && $preventivoId > 0) {
    try {
        require_once __DIR__ . '/../../../vendor/autoload.php';
        require_once __DIR__ . '/../../../src/payments/stripe.php';
        require_once __DIR__ . '/../../../src/db.php';

        $intent = retrievePaymentIntent($paymentIntentId);

        if ($intent && $intent->status === 'succeeded') {
            $brand      = null;
            $ultimi4    = null;
            $metodo     = null;
            $receiptUrl = null;
            $charges    = $intent->charges->data ?? [];
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

            $pdo->prepare("
                UPDATE pagamenti SET
                    stato              = 'paid',
                    stripe_metodo      = ?,
                    stripe_ultimi_4    = ?,
                    stripe_brand       = ?,
                    stripe_receipt_url = ?,
                    aggiornato_il      = CURRENT_TIMESTAMP
                WHERE stripe_payment_intent_id = ?
                  AND stato != 'paid'
            ")->execute([$metodo, $ultimi4, $brand, $receiptUrl, $paymentIntentId]);

            $pdo->prepare("
                UPDATE preventivi SET
                    pagamento_stato = 'pagato',
                    stato           = 'confermato',
                    aggiornato_il   = CURRENT_TIMESTAMP
                WHERE id = ?
                  AND pagamento_stato != 'pagato'
            ")->execute([$preventivoId]);

            $success = true;
        }
    } catch (Exception $e) {
        // In caso di errore, mostra comunque la pagina senza bloccare
        $success = ($redirectStatus === 'succeeded');
    }
} elseif ($sessionId) {
    $success = true;
} elseif ($paymentId) {
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include __DIR__ . '/../../includes/head.php'; ?>
</head>

<body>
    <div class="auth-container">
        <div class="auth-box" style="text-align: center;">
            <?php if ($success): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">&#10004;&#65039;</div>
                <h2>Pagamento Completato!</h2>
                <p style="margin: 1rem 0;">Il tuo pagamento è stato elaborato con successo.</p>
                <div class="alert alert-success">
                    <p>Riceverai una conferma via email a breve.</p>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="/dashboard/ordini" class="btn btn-primary">Visualizza Ordini</a>
                    <a href="/" class="btn btn-secondary">Torna alla Home</a>
                </div>
            <?php else: ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">&#9888;&#65039;</div>
                <h2>Errore nel Pagamento</h2>
                <p style="margin: 1rem 0;">Si è verificato un problema durante l'elaborazione del pagamento.</p>
                <div class="alert alert-error">
                    <p>Riprova o contatta il supporto se il problema persiste.</p>
                </div>
                <div style="margin-top: 2rem;">
                    <a href="/pagamento" class="btn btn-primary">Riprova</a>
                    <a href="/" class="btn btn-secondary">Torna alla Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../../includes/whatsapp-button.php'; ?>
</body>

</html>