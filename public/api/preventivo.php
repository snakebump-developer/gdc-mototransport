<?php

/**
 * API per la creazione di un preventivo dal form multi-step.
 * Accetta POST JSON, valida i campi obbligatori, salva in DB.
 */

header('Content-Type: application/json; charset=utf-8');

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Metodo non consentito']);
    exit;
}

// Decodifica payload JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Payload JSON non valido']);
    exit;
}

// Campi obbligatori
$required = [
    'marca_moto',
    'modello_moto',
    'cilindrata',
    'indirizzo_ritiro',
    'indirizzo_consegna',
    'nome_cliente',
    'email_cliente',
    'telefono_cliente',
    'data_ritiro'
];

foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(422);
        echo json_encode(['error' => "Campo obbligatorio mancante: $field"]);
        exit;
    }
}

// Validazione email
if (!filter_var($data['email_cliente'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Email non valida']);
    exit;
}

// Validazione data_ritiro (deve essere nel futuro)
$dataRitiro = $data['data_ritiro'];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataRitiro)) {
    http_response_code(422);
    echo json_encode(['error' => 'Formato data non valido (atteso YYYY-MM-DD)']);
    exit;
}
if ($dataRitiro <= date('Y-m-d')) {
    http_response_code(422);
    echo json_encode(['error' => 'La data di ritiro deve essere nel futuro']);
    exit;
}

// Sanitizzazione valori numerici
$distanzaKm    = isset($data['distanza_km'])    ? (float) $data['distanza_km']    : null;
$prezzoBase    = isset($data['prezzo_base'])    ? (float) $data['prezzo_base']    : null;
$prezzoFinale  = isset($data['prezzo_finale'])  ? (float) $data['prezzo_finale']  : null;
$borseLaterali = isset($data['borse_laterali']) ? (float) $data['borse_laterali'] : 0.0;

$tipoConsegna  = in_array($data['tipo_consegna'] ?? '', ['Standard', 'Express', 'Urgente'])
    ? $data['tipo_consegna']
    : 'Standard';

// Collega all'utente loggato (se presente)
$config = require __DIR__ . '/../../src/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['user_id'] ?? null;

// Salva nel DB
try {
    require_once __DIR__ . '/../../src/db.php';

    $stmt = $pdo->prepare("
        INSERT INTO preventivi
            (user_id, nome_cliente, email_cliente, telefono_cliente,
             codice_fiscale_cliente, indirizzo_ritiro, indirizzo_consegna,
             distanza_km, marca_moto, modello_moto, cilindrata,
             borse_laterali, tipo_consegna, data_ritiro,
             prezzo_base, prezzo_finale, stato)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'inviato')
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
        $borseLaterali,
        $tipoConsegna,
        $dataRitiro,
        $prezzoBase,
        $prezzoFinale,
    ]);

    $preventivoId = (int) $pdo->lastInsertId();

    // Se la moto non è nel catalogo ufficiale, salvarla come bozza per revisione admin
    $marcaBozza   = htmlspecialchars(strip_tags($data['marca_moto']),   ENT_QUOTES, 'UTF-8');
    $modelloBozza = htmlspecialchars(strip_tags($data['modello_moto']), ENT_QUOTES, 'UTF-8');
    $chkCat = $pdo->prepare("SELECT id FROM catalogo_moto WHERE marca=? AND modello=? LIMIT 1");
    $chkCat->execute([$marcaBozza, $modelloBozza]);
    if (!$chkCat->fetch()) {
        $pdo->prepare("INSERT OR IGNORE INTO moto_bozze (marca, modello) VALUES (?, ?)")
            ->execute([$marcaBozza, $modelloBozza]);
    }

    // Se l'utente è loggato e ha fornito un CF, salvarlo nel profilo se ancora assente
    $cfCliente = trim($data['codice_fiscale_cliente'] ?? '');
    if ($userId && $cfCliente !== '') {
        $cfCliente = strtoupper($cfCliente);
        if (preg_match('/^[A-Z0-9]{11,16}$/', $cfCliente)) {
            $chk = $pdo->prepare("SELECT codice_fiscale_azienda FROM utenti WHERE id = ?");
            $chk->execute([$userId]);
            $profileCf = $chk->fetchColumn();
            if (empty($profileCf)) {
                $upd = $pdo->prepare("UPDATE utenti SET codice_fiscale_azienda = ?, aggiornato_il = CURRENT_TIMESTAMP WHERE id = ?");
                $upd->execute([$cfCliente, $userId]);
            }
        }
    }

    echo json_encode(['success' => true, 'id' => $preventivoId]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel salvataggio. Riprova.']);
}
