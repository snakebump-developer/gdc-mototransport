<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';

// Questa pagina gestisce il successo del pagamento
$sessionId = $_GET['session_id'] ?? null;
$paymentId = $_GET['payment_id'] ?? null;
$payerId = $_GET['PayerID'] ?? null;

$success = false;
$ordineId = null;

if ($sessionId) {
    // Pagamento Stripe
    // require_once __DIR__ . '/../src/payments/stripe.php';
    // Verifica il pagamento e aggiorna l'ordine
    $success = true;
} elseif ($paymentId && $payerId) {
    // Pagamento PayPal
    // require_once __DIR__ . '/../src/payments/paypal.php';
    // Esegui e verifica il pagamento
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Completato - Starter Kit</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box" style="text-align: center;">
            <?php if ($success): ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                <h2>Pagamento Completato!</h2>
                <p style="margin: 1rem 0;">Il tuo pagamento è stato elaborato con successo.</p>
                
                <div class="alert alert-success">
                    <p>Riceverai una conferma via email a breve.</p>
                </div>
                
                <div style="margin-top: 2rem;">
                    <a href="dashboard.php?section=orders" class="btn btn-primary">Visualizza Ordini</a>
                    <a href="index.php" class="btn btn-secondary">Torna alla Home</a>
                </div>
            <?php else: ?>
                <div style="font-size: 4rem; margin-bottom: 1rem;">⚠️</div>
                <h2>Errore nel Pagamento</h2>
                <p style="margin: 1rem 0;">Si è verificato un problema durante l'elaborazione del pagamento.</p>
                
                <div class="alert alert-error">
                    <p>Riprova o contatta il supporto se il problema persiste.</p>
                </div>
                
                <div style="margin-top: 2rem;">
                    <a href="payment.php" class="btn btn-primary">Riprova</a>
                    <a href="index.php" class="btn btn-secondary">Torna alla Home</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
