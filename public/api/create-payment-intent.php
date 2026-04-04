<?php

/**
 * API: Crea un Stripe PaymentIntent dopo la conferma del preventivo.
 *
 * Flusso:
 *   1. Riceve POST JSON con i dati del preventivo
 *   2. Salva il preventivo nel DB (o recupera uno esistente)
 *   3. Crea il PaymentIntent su Stripe (importo calcolato SERVER-SIDE)
 *   4. Salva il record in `pagamenti`
 *   5. Restituisce { clientSecret, preventivoId, pagamentoId }
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload JSON non valido']);
    exit;
}

// --- Campi obbligatori ---
$required = [
    'marca_moto',
    'modello_moto',
    'cilindrata',
    'indirizzo_ritiro',
    'indirizzo_consegna',
    'nome_cliente',
    'email_cliente',
    'telefono_cliente',
    'data_ritiro',
    'prezzo_finale'
];

foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(422);
        echo json_encode(['error' => "Campo obbligatorio mancante: $field"]);
        exit;
    }
}

if (!filter_var($data['email_cliente'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Email non valida']);
    exit;
}

// --- Importo: calcolato server-side con la STESSA formula di route-calc.php ---
// Costanti speculari a route-calc.php (aggiornare entrambi i file se si cambia la tariffa)
const FUEL_CONSUMPTION = 12;    // L/100km furgone benzina
const FUEL_PRICE       = 1.80;  // €/L
const TOLL_RATE        = 0.07;  // €/km pedaggi
const MIN_TRANSPORT    = 50.0;  // costo minimo trasporto (€)
const BASE_FALLBACK    = 175.0; // prezzo fisso quando non c'è distanza

$DELIVERY_SURCHARGE = ['Standard' => 0, 'Express' => 50, 'Urgente' => 100];

$distanzaKm    = isset($data['distanza_km'])    ? (float) $data['distanza_km']    : null;
$borseLaterali = isset($data['borse_laterali']) ? (float) $data['borse_laterali'] : 0.0;
$tipoConsegna  = in_array($data['tipo_consegna'] ?? '', ['Standard', 'Express', 'Urgente'])
    ? $data['tipo_consegna'] : 'Standard';

// Identica a route-calc.php: fuel + pedaggi, minimo MIN_TRANSPORT
if ($distanzaKm !== null && $distanzaKm > 0) {
    $fuelCost   = ($distanzaKm * FUEL_CONSUMPTION / 100) * FUEL_PRICE;
    $tollCost   = $distanzaKm * TOLL_RATE;
    $prezzoBase = max($fuelCost + $tollCost, MIN_TRANSPORT);
} else {
    // Nessuna distanza: usa il prezzo fisso di fallback
    $prezzoBase = BASE_FALLBACK;
}

// Validazione borse (valori ammessi: 0, 30, 70)
$borse = in_array((int)$borseLaterali, [0, 30, 70]) ? (float)$borseLaterali : 0.0;

$prezzoFinale = round($prezzoBase + ($DELIVERY_SURCHARGE[$tipoConsegna]) + $borse, 2);

// Importo minimo 1€ per Stripe
if ($prezzoFinale < 1.0) {
    http_response_code(422);
    echo json_encode(['error' => 'Importo non valido']);
    exit;
}

// --- Sessione / utente ---
$config = require __DIR__ . '/../../src/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['user_id'] ?? null;

try {
    require_once __DIR__ . '/../../src/db.php';

    // 1. Salva il preventivo
    $dataRitiro = $data['data_ritiro'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataRitiro) || $dataRitiro <= date('Y-m-d')) {
        http_response_code(422);
        echo json_encode(['error' => 'Data di ritiro non valida']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO preventivi
            (user_id, nome_cliente, email_cliente, telefono_cliente,
             codice_fiscale_cliente, indirizzo_ritiro, indirizzo_consegna,
             distanza_km, marca_moto, modello_moto, cilindrata,
             borse_laterali, tipo_consegna, data_ritiro,
             prezzo_base, prezzo_finale, stato, pagamento_stato)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'inviato','non_pagato')
    ");
    $stmt->execute([
        $userId,
        htmlspecialchars(strip_tags($data['nome_cliente']), ENT_QUOTES, 'UTF-8'),
        $data['email_cliente'],
        htmlspecialchars(strip_tags($data['telefono_cliente']), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(strip_tags($data['codice_fiscale_cliente'] ?? ''), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(strip_tags($data['indirizzo_ritiro']), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(strip_tags($data['indirizzo_consegna']), ENT_QUOTES, 'UTF-8'),
        $distanzaKm,
        htmlspecialchars(strip_tags($data['marca_moto']), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(strip_tags($data['modello_moto']), ENT_QUOTES, 'UTF-8'),
        htmlspecialchars(strip_tags($data['cilindrata']), ENT_QUOTES, 'UTF-8'),
        $borse,
        $tipoConsegna,
        $dataRitiro,
        round($prezzoBase, 2),
        $prezzoFinale,
    ]);
    $preventivoId = (int) $pdo->lastInsertId();

    // 2. Crea PaymentIntent su Stripe
    require_once __DIR__ . '/../../src/payments/stripe.php';
    $intent = createPaymentIntent($preventivoId, $prezzoFinale, $data['email_cliente']);

    // 3. Salva in `pagamenti`
    $stmt2 = $pdo->prepare("
        INSERT INTO pagamenti
            (preventivo_id, stripe_payment_intent_id, importo, valuta, stato)
        VALUES (?,?,?,?,?)
    ");
    $stmt2->execute([
        $preventivoId,
        $intent->id,
        $prezzoFinale,
        'eur',
        'pending',
    ]);
    $pagamentoId = (int) $pdo->lastInsertId();

    // 4. Collega il pagamento al preventivo
    $pdo->prepare("UPDATE preventivi SET stripe_payment_intent_id=?, pagamento_id=? WHERE id=?")
        ->execute([$intent->id, $pagamentoId, $preventivoId]);

    echo json_encode([
        'success'      => true,
        'clientSecret' => $intent->client_secret,
        'preventivoId' => $preventivoId,
        'pagamentoId'  => $pagamentoId,
        'importo'      => $prezzoFinale,
    ]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(502);
    echo json_encode(['error' => 'Errore Stripe: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore interno. Riprova.']);
}
