<?php

/**
 * API catalogo moto.
 *
 * GET /api/moto-catalogo.php?action=marche
 *   → Restituisce tutte le marche (catalogo ufficiale).
 *
 * GET /api/moto-catalogo.php?action=modelli&marca=Ducati
 *   → Restituisce i modelli di una marca (catalogo ufficiale).
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

$action = trim($_GET['action'] ?? '');

require_once __DIR__ . '/../../src/db.php';

if ($action === 'marche') {
    $rows = $pdo->query(
        "SELECT DISTINCT marca FROM catalogo_moto ORDER BY marca COLLATE NOCASE"
    )->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'marche' => $rows]);
} elseif ($action === 'modelli') {
    $marca = trim($_GET['marca'] ?? '');
    if ($marca === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Parametro marca mancante']);
        exit;
    }

    $stmt = $pdo->prepare(
        "SELECT modello FROM catalogo_moto WHERE marca = ? ORDER BY modello COLLATE NOCASE"
    );
    $stmt->execute([$marca]);
    $modelli = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'modelli' => $modelli]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Azione non valida']);
}
