<?php
/**
 * Configurazione e helper per Stripe
 * 
 * Per utilizzare Stripe:
 * 1. Installa la libreria: composer require stripe/stripe-php
 * 2. Ottieni le tue API keys da https://dashboard.stripe.com/apikeys
 * 3. Configura le chiavi nel file config.php
 */

// Esempio di utilizzo (da decommentare dopo l'installazione):
// require_once __DIR__ . '/../vendor/autoload.php';
// \Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY') ?: 'sk_test_...');

/**
 * Crea una sessione di checkout Stripe
 */
function createStripeCheckout($orderId, $amount, $currency = 'eur', $successUrl, $cancelUrl) {
    // Esempio di implementazione
    /*
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => 'Ordine #' . $orderId,
                ],
                'unit_amount' => $amount * 100, // Stripe usa centesimi
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $cancelUrl,
        'metadata' => [
            'order_id' => $orderId
        ]
    ]);
    
    return $session;
    */
    
    throw new Exception("Stripe non ancora configurato. Segui le istruzioni nel file.");
}

/**
 * Verifica il pagamento Stripe tramite webhook
 */
function verifyStripeWebhook($payload, $signature) {
    // Esempio di implementazione
    /*
    $endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET');
    
    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $signature, $endpoint_secret
        );
        
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id;
            
            // Aggiorna lo stato dell'ordine
            require_once __DIR__ . '/orders.php';
            updateOrderStatus($orderId, 'completed', $session->id);
            
            return true;
        }
    } catch(\UnexpectedValueException $e) {
        return false;
    }
    */
    
    return false;
}

/**
 * Recupera i dettagli di una transazione Stripe
 */
function getStripeTransaction($transactionId) {
    // Esempio di implementazione
    /*
    try {
        $session = \Stripe\Checkout\Session::retrieve($transactionId);
        return [
            'id' => $session->id,
            'amount' => $session->amount_total / 100,
            'currency' => $session->currency,
            'status' => $session->payment_status,
            'created' => date('Y-m-d H:i:s', $session->created)
        ];
    } catch (\Stripe\Exception\ApiErrorException $e) {
        return null;
    }
    */
    
    return null;
}
