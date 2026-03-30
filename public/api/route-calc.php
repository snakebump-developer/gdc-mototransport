<?php

/**
 * API proxy per calcolo rotta e costi di trasporto.
 * Riceve origin e destination (lat,lng), chiama Google Directions API,
 * calcola costi carburante e pedaggi, restituisce JSON.
 */

header('Content-Type: application/json; charset=utf-8');

// Carica configurazione
$config = require __DIR__ . '/../../src/config.php';
$apiKey = $config['google_maps_api_key'] ?? '';

if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'API key non configurata']);
    exit;
}

// --- Costanti configurabili ---
const FUEL_CONSUMPTION_L_PER_100KM = 12;   // Consumo furgone benzina (L/100km)
const FUEL_PRICE_EUR_PER_L = 1.80;          // Prezzo benzina (€/L)
const TOLL_RATE_EUR_PER_KM = 0.07;          // Stima pedaggio autostradale (€/km)
const MIN_TRANSPORT_COST = 50;              // Costo minimo trasporto (€)

// --- Input validation ---
$originLat  = filter_input(INPUT_GET, 'origin_lat', FILTER_VALIDATE_FLOAT);
$originLng  = filter_input(INPUT_GET, 'origin_lng', FILTER_VALIDATE_FLOAT);
$destLat    = filter_input(INPUT_GET, 'dest_lat', FILTER_VALIDATE_FLOAT);
$destLng    = filter_input(INPUT_GET, 'dest_lng', FILTER_VALIDATE_FLOAT);

if (
    $originLat === false || $originLng === false || $destLat === false || $destLng === false
    || $originLat === null || $originLng === null || $destLat === null || $destLng === null
) {
    http_response_code(400);
    echo json_encode(['error' => 'Parametri origin_lat, origin_lng, dest_lat, dest_lng richiesti e devono essere numerici']);
    exit;
}

// Validazione range coordinate Italia (approssimativo)
if (
    $originLat < 35 || $originLat > 48 || $originLng < 5 || $originLng > 19
    || $destLat < 35 || $destLat > 48 || $destLng < 5 || $destLng > 19
) {
    http_response_code(400);
    echo json_encode(['error' => 'Le coordinate devono essere all\'interno del territorio italiano']);
    exit;
}

// --- Chiama Google Directions API ---
$origin = $originLat . ',' . $originLng;
$destination = $destLat . ',' . $destLng;

$url = 'https://maps.googleapis.com/maps/api/directions/json?' . http_build_query([
    'origin'      => $origin,
    'destination'  => $destination,
    'mode'        => 'driving',
    'language'    => 'it',
    'region'      => 'it',
    'key'         => $apiKey
]);

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Impossibile contattare Google Directions API']);
    exit;
}

$data = json_decode($response, true);

if (!$data || ($data['status'] ?? '') !== 'OK' || empty($data['routes'])) {
    http_response_code(502);
    echo json_encode(['error' => 'Nessun percorso trovato', 'status' => $data['status'] ?? 'UNKNOWN']);
    exit;
}

$route = $data['routes'][0];
$leg = $route['legs'][0];

$distanceMeters = $leg['distance']['value'];  // metri
$durationSeconds = $leg['duration']['value'];  // secondi
$distanceKm = round($distanceMeters / 1000, 1);

// Durata leggibile
$hours = floor($durationSeconds / 3600);
$minutes = round(($durationSeconds % 3600) / 60);
if ($hours > 0) {
    $durationText = $hours . ' or' . ($hours > 1 ? 'e' : 'a') . ' ' . $minutes . ' min';
} else {
    $durationText = $minutes . ' min';
}

// --- Calcolo costi ---
$fuelCost = round(($distanceKm * FUEL_CONSUMPTION_L_PER_100KM / 100) * FUEL_PRICE_EUR_PER_L, 2);
$tollCost = round($distanceKm * TOLL_RATE_EUR_PER_KM, 2);
$subtotal = $fuelCost + $tollCost;
$totalCost = max($subtotal, MIN_TRANSPORT_COST);

// Polyline codificata per disegnare la rotta sulla mappa
$polyline = $route['overview_polyline']['points'] ?? '';

echo json_encode([
    'distance_km'   => $distanceKm,
    'duration_text'  => $durationText,
    'fuel_cost'      => $fuelCost,
    'toll_cost'      => $tollCost,
    'total_cost'     => $totalCost,
    'polyline'       => $polyline,
    'origin_address' => $leg['start_address'] ?? '',
    'dest_address'   => $leg['end_address'] ?? ''
]);
