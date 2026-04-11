<?php
require_once __DIR__ . '/../../src/auth.php';

header('Content-Type: application/json');

// Solo admin possono eseguire il seed
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$config   = require __DIR__ . '/../../src/config.php';
$dbConf   = $config['db'];
$jsonPath = __DIR__ . '/../../database/catalogo-moto.json';

if (!file_exists($jsonPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'File JSON catalogo non trovato: ' . $jsonPath]);
    exit;
}

$json = json_decode(file_get_contents($jsonPath), true);
if (!is_array($json)) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore parsing JSON: ' . json_last_error_msg()]);
    exit;
}

try {
    $dsn = "mysql:host={$dbConf['host']};port={$dbConf['port']};dbname={$dbConf['name']};charset={$dbConf['charset']}";
    $pdo = new PDO($dsn, $dbConf['user'], $dbConf['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT IGNORE INTO catalogo_moto (marca, modello) VALUES (?, ?)");

    $inserted = 0;
    $pdo->beginTransaction();

    foreach ($json as $entry) {
        $marca = trim($entry['marca'] ?? '');
        if (empty($marca) || !isset($entry['moto']) || !is_array($entry['moto'])) continue;

        foreach ($entry['moto'] as $modello) {
            $modello = trim($modello);
            if (empty($modello)) continue;
            $stmt->execute([$marca, $modello]);
            if ($stmt->rowCount() > 0) $inserted++;
        }
    }

    $pdo->commit();

    $total = (int)$pdo->query("SELECT COUNT(*) FROM catalogo_moto")->fetchColumn();

    echo json_encode([
        'success'  => true,
        'inserted' => $inserted,
        'total'    => $total,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore DB: ' . $e->getMessage()]);
}
