<?php

/**
 * API: Salva un preventivo come bozza (stato='bozza') valida 10 giorni.
 * Se l'utente non è loggato, restituisce 401.
 * Se esiste già una bozza attiva per lo stesso utente con gli stessi dati chiave,
 * la sovrascrive (upsert by marca+modello+ritiro+consegna) per evitare duplicati.
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

// Sessione / autenticazione
$config = require __DIR__ . '/../../src/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Devi essere autenticato per salvare un preventivo.']);
    exit;
}

// Payload
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload JSON non valido']);
    exit;
}

// Campi obbligatori
$required = ['marca_moto', 'modello_moto', 'cilindrata', 'indirizzo_ritiro', 'indirizzo_consegna', 'data_ritiro'];
foreach ($required as $f) {
    if (empty($data[$f])) {
        http_response_code(422);
        echo json_encode(['error' => "Campo obbligatorio mancante: $f"]);
        exit;
    }
}

// Validazione data
$dataRitiro = $data['data_ritiro'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataRitiro) || $dataRitiro <= date('Y-m-d')) {
    http_response_code(422);
    echo json_encode(['error' => 'La data di ritiro deve essere nel futuro']);
    exit;
}

$tipoConsegna = in_array($data['tipo_consegna'] ?? '', ['Standard', 'Express', 'Urgente'])
    ? $data['tipo_consegna'] : 'Standard';

$distanzaKm    = isset($data['distanza_km'])    ? (float) $data['distanza_km']    : null;
$prezzoBase    = isset($data['prezzo_base'])    ? (float) $data['prezzo_base']    : null;
$prezzoFinale  = isset($data['prezzo_finale'])  ? (float) $data['prezzo_finale']  : null;
$borseLaterali = isset($data['borse_laterali']) ? (float) $data['borse_laterali'] : 0.0;

// route_data: tutto il payload di route-calc per poter ricalcolare in seguito
$routeDataJson = isset($data['route_data']) ? json_encode($data['route_data']) : null;

// Scadenza: il minore tra +10 giorni e (data_ritiro − 5 giorni)
// Così la bozza non sopravvive inutilmente oltre la finestra utile per prenotare.
$tsDefault = strtotime('+10 days');
$tsRitiro  = strtotime($dataRitiro . ' -5 days');
$tsScadenza = min($tsDefault, $tsRitiro);

// Se la finestra utile è già scaduta (es. ritiro tra meno di 5 giorni)
if ($tsScadenza <= time()) {
    http_response_code(422);
    echo json_encode(['error' => 'La data di ritiro è troppo vicina: non è possibile salvare una bozza con meno di 5 giorni di anticipo.']);
    exit;
}

$scadenzaIl = date('Y-m-d H:i:s', $tsScadenza);

try {
    require_once __DIR__ . '/../../src/db.php';

    // Cerca una bozza attiva già esistente per lo stesso utente + stessi indirizzi + stessa moto
    $existing = $pdo->prepare("
        SELECT id FROM preventivi
        WHERE user_id = ?
          AND stato = 'bozza'
          AND LOWER(marca_moto)       = LOWER(?)
          AND LOWER(modello_moto)     = LOWER(?)
          AND LOWER(indirizzo_ritiro) = LOWER(?)
          AND LOWER(indirizzo_consegna) = LOWER(?)
        LIMIT 1
    ");
    $existing->execute([
        $userId,
        trim($data['marca_moto']),
        trim($data['modello_moto']),
        trim($data['indirizzo_ritiro']),
        trim($data['indirizzo_consegna']),
    ]);
    $row = $existing->fetch();

    $marca   = htmlspecialchars(strip_tags($data['marca_moto']),  ENT_QUOTES, 'UTF-8');
    $modello = htmlspecialchars(strip_tags($data['modello_moto']), ENT_QUOTES, 'UTF-8');
    $cc      = htmlspecialchars(strip_tags($data['cilindrata']),   ENT_QUOTES, 'UTF-8');
    $ritiro  = htmlspecialchars(strip_tags($data['indirizzo_ritiro']),   ENT_QUOTES, 'UTF-8');
    $consegna = htmlspecialchars(strip_tags($data['indirizzo_consegna']), ENT_QUOTES, 'UTF-8');
    $nome    = htmlspecialchars(strip_tags($data['nome_cliente']   ?? ''), ENT_QUOTES, 'UTF-8');
    $email   = filter_var($data['email_cliente']  ?? '', FILTER_VALIDATE_EMAIL) ?: '';
    $telefono = htmlspecialchars(strip_tags($data['telefono_cliente'] ?? ''), ENT_QUOTES, 'UTF-8');
    $cf      = htmlspecialchars(strip_tags($data['codice_fiscale_cliente'] ?? ''), ENT_QUOTES, 'UTF-8');

    if ($row) {
        // Aggiorna la bozza esistente
        $stmt = $pdo->prepare("
            UPDATE preventivi SET
                cilindrata = ?, tipo_consegna = ?, data_ritiro = ?,
                distanza_km = ?, borse_laterali = ?,
                prezzo_base = ?, prezzo_finale = ?,
                nome_cliente = ?, email_cliente = ?, telefono_cliente = ?,
                codice_fiscale_cliente = ?,
                route_data_json = ?,
                scadenza_il = ?,
                aggiornato_il = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([
            $cc,
            $tipoConsegna,
            $dataRitiro,
            $distanzaKm,
            $borseLaterali,
            $prezzoBase,
            $prezzoFinale,
            $nome,
            $email,
            $telefono,
            $cf,
            $routeDataJson,
            $scadenzaIl,
            $row['id'],
        ]);
        $preventivoId = (int) $row['id'];
    } else {
        // Inserisce nuova bozza
        $stmt = $pdo->prepare("
            INSERT INTO preventivi
                (user_id, stato, marca_moto, modello_moto, cilindrata,
                 tipo_consegna, data_ritiro,
                 indirizzo_ritiro, indirizzo_consegna,
                 distanza_km, borse_laterali,
                 prezzo_base, prezzo_finale,
                 nome_cliente, email_cliente, telefono_cliente, codice_fiscale_cliente,
                 route_data_json, scadenza_il)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $userId,
            'bozza',
            $marca,
            $modello,
            $cc,
            $tipoConsegna,
            $dataRitiro,
            $ritiro,
            $consegna,
            $distanzaKm,
            $borseLaterali,
            $prezzoBase,
            $prezzoFinale,
            $nome,
            $email,
            $telefono,
            $cf,
            $routeDataJson,
            $scadenzaIl,
        ]);
        $preventivoId = (int) $pdo->lastInsertId();
    }

    echo json_encode([
        'success'     => true,
        'id'          => $preventivoId,
        'scadenza_il' => $scadenzaIl,
    ]);
} catch (Exception $e) {
    error_log('[save-draft] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel salvataggio. Riprova.']);
}
