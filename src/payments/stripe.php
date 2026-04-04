<?php

/**
 * Helpers Stripe - integrazione Payment Intent + webhook
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/../config.php';
$stripeSecretKey = $config['stripe']['secret_key'];

if (empty($stripeSecretKey)) {
    throw new RuntimeException('STRIPE_SECRET_KEY non configurata in .env');
}

\Stripe\Stripe::setApiKey($stripeSecretKey);

/**
 * Crea un PaymentIntent per un preventivo.
 * Ritorna l'oggetto PaymentIntent di Stripe.
 *
 * @param  int    $preventivoId  ID del preventivo nel DB
 * @param  float  $importoEuro   Importo in euro (es. 175.00)
 * @param  string $emailCliente  Email per la ricevuta Stripe
 * @return \Stripe\PaymentIntent
 */
function createPaymentIntent(int $preventivoId, float $importoEuro, string $emailCliente): \Stripe\PaymentIntent
{
    $importoCentesimi = (int) round($importoEuro * 100);

    return \Stripe\PaymentIntent::create([
        'amount'               => $importoCentesimi,
        'currency'             => 'eur',
        'payment_method_types' => ['card'],
        'receipt_email'        => $emailCliente,
        'metadata'             => [
            'preventivo_id' => $preventivoId,
            'source'        => 'gdc-mototransport',
        ],
        'description'          => 'Trasporto moto - Preventivo #' . $preventivoId,
    ]);
}

/**
 * Recupera un PaymentIntent da Stripe per ID.
 *
 * @param  string $paymentIntentId
 * @return \Stripe\PaymentIntent|null
 */
function retrievePaymentIntent(string $paymentIntentId): ?\Stripe\PaymentIntent
{
    try {
        return \Stripe\PaymentIntent::retrieve($paymentIntentId);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        return null;
    }
}

/**
 * Verifica e decodifica un evento webhook Stripe.
 * Ritorna l'evento oppure null se la firma non è valida.
 *
 * @param  string $payload    Raw request body
 * @param  string $signature  Valore di Stripe-Signature header
 * @param  string $secret     Webhook signing secret
 * @return \Stripe\Event|null
 */
function verifyStripeWebhook(string $payload, string $signature, string $secret): ?\Stripe\Event
{
    try {
        return \Stripe\Webhook::constructEvent($payload, $signature, $secret);
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        return null;
    } catch (\UnexpectedValueException $e) {
        return null;
    }
}
