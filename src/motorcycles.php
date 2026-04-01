<?php
require_once __DIR__ . '/db.php';

// =========================================================
// MOTO SALVATE — accessibili da utenti e professionisti
// =========================================================

function saveMotorcycle(int $userId, array $data): int
{
    global $pdo;

    _validateMotorcycleData($data);

    $stmt = $pdo->prepare("
        INSERT INTO moto_salvate (user_id, marca, modello, anno, cilindrata, targa, colore, note)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $data['marca'],
        $data['modello'],
        !empty($data['anno'])       ? (int)$data['anno']       : null,
        !empty($data['cilindrata']) ? (int)$data['cilindrata'] : null,
        !empty($data['targa'])      ? strtoupper(trim($data['targa'])) : null,
        $data['colore'] ?? null,
        $data['note']   ?? null,
    ]);

    return (int)$pdo->lastInsertId();
}

function getUserMotorcycles(int $userId): array
{
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM moto_salvate
        WHERE user_id = ?
        ORDER BY creato_il DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getMotorcycleById(int $id, int $userId): array|false
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM moto_salvate WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    return $stmt->fetch();
}

function updateMotorcycle(int $id, int $userId, array $data): bool
{
    global $pdo;

    // Verifica proprietà
    if (!getMotorcycleById($id, $userId)) {
        throw new Exception("Moto non trovata.");
    }

    _validateMotorcycleData($data);

    $stmt = $pdo->prepare("
        UPDATE moto_salvate
        SET marca       = ?,
            modello     = ?,
            anno        = ?,
            cilindrata  = ?,
            targa       = ?,
            colore      = ?,
            note        = ?,
            aggiornato_il = CURRENT_TIMESTAMP
        WHERE id = ? AND user_id = ?
    ");
    return $stmt->execute([
        $data['marca'],
        $data['modello'],
        !empty($data['anno'])       ? (int)$data['anno']       : null,
        !empty($data['cilindrata']) ? (int)$data['cilindrata'] : null,
        !empty($data['targa'])      ? strtoupper(trim($data['targa'])) : null,
        $data['colore'] ?? null,
        $data['note']   ?? null,
        $id,
        $userId,
    ]);
}

function deleteMotorcycle(int $id, int $userId): bool
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM moto_salvate WHERE id = ? AND user_id = ?");
    return $stmt->execute([$id, $userId]);
}

// Validazione interna
function _validateMotorcycleData(array $data): void
{
    if (empty(trim($data['marca'] ?? ''))) {
        throw new Exception("La marca della moto è obbligatoria.");
    }
    if (empty(trim($data['modello'] ?? ''))) {
        throw new Exception("Il modello della moto è obbligatorio.");
    }
    if (!empty($data['anno'])) {
        $anno = (int)$data['anno'];
        if ($anno < 1900 || $anno > (int)date('Y') + 1) {
            throw new Exception("Anno moto non valido.");
        }
    }
    if (!empty($data['cilindrata'])) {
        $cc = (int)$data['cilindrata'];
        if ($cc < 50 || $cc > 3000) {
            throw new Exception("Cilindrata non valida (50-3000 cc).");
        }
    }
    if (!empty($data['targa'])) {
        // Targa italiana: 2 lettere + 3 cifre + 2 lettere oppure vecchio formato
        $t = strtoupper(trim($data['targa']));
        if (!preg_match('/^[A-Z]{2}[0-9]{3}[A-Z]{2}$|^[A-Z]{2}[0-9]{5}$/', $t)) {
            throw new Exception("Formato targa non valido.");
        }
    }
}
