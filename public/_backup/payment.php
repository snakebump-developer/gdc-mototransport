<?php
/**
 * File di esempio per gestire i pagamenti
 * Questo file mostra come integrare Stripe e PayPal nel tuo progetto
 */

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../orders.php';

requireLogin();

// Esempio di creazione ordine con pagamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_payment'])) {
    $metodoPagamento = $_POST['metodo_pagamento']; // 'stripe' o 'paypal'
    $totale = floatval($_POST['totale']);
    
    // Crea l'ordine nel database
    $items = [
        [
            'descrizione' => 'Prodotto di esempio',
            'quantita' => 1,
            'prezzo_unitario' => $totale
        ]
    ];
    
    $ordineId = createOrder($_SESSION['user_id'], $totale, $metodoPagamento, $items);
    
    // URL di ritorno
    $successUrl = 'http://localhost/payment-success.php';
    $cancelUrl = 'http://localhost/payment-cancel.php';
    
    try {
        if ($metodoPagamento === 'stripe') {
            require_once __DIR__ . '/stripe.php';
            $session = createStripeCheckout($ordineId, $totale, 'eur', $successUrl, $cancelUrl);
            // Reindirizza a Stripe Checkout
            header('Location: ' . $session->url);
            exit;
        } elseif ($metodoPagamento === 'paypal') {
            require_once __DIR__ . '/paypal.php';
            $payment = createPayPalPayment($ordineId, $totale, 'EUR', $successUrl, $cancelUrl);
            // Reindirizza a PayPal
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() === 'approval_url') {
                    header('Location: ' . $link->getHref());
                    exit;
                }
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Starter Kit</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Procedi al Pagamento</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <input type="hidden" name="create_payment" value="1">
                
                <div class="form-group">
                    <label for="totale">Importo (€)</label>
                    <input type="number" id="totale" name="totale" step="0.01" min="0.01" required value="10.00">
                </div>
                
                <div class="form-group">
                    <label>Metodo di Pagamento</label>
                    <div style="margin-top: 0.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem;">
                            <input type="radio" name="metodo_pagamento" value="stripe" checked>
                            Carta di Credito (Stripe)
                        </label>
                        <label style="display: block;">
                            <input type="radio" name="metodo_pagamento" value="paypal">
                            PayPal
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Procedi al Pagamento</button>
            </form>
            
            <div class="auth-footer">
                <p><a href="../dashboard.php">Torna alla Dashboard</a></p>
            </div>
        </div>
    </div>
</body>
</html>
