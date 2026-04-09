<?php
require_once __DIR__ . '/db.php';

function getAllUsers($limit = 50, $offset = 0, string $search = '')
{
    global $pdo;
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $pdo->prepare("
            SELECT id, username, email, nome, cognome, ruolo, creato_il
            FROM utenti
            WHERE ruolo IN ('user', 'admin')
              AND (username LIKE ? OR email LIKE ? OR nome LIKE ? OR cognome LIKE ?)
            ORDER BY creato_il DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$like, $like, $like, $like, $limit, $offset]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id, username, email, nome, cognome, ruolo, creato_il
            FROM utenti
            WHERE ruolo IN ('user', 'admin')
            ORDER BY creato_il DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
    }
    return $stmt->fetchAll();
}

function countAllUsers(string $search = ''): int
{
    global $pdo;
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM utenti
            WHERE ruolo IN ('user', 'admin')
              AND (username LIKE ? OR email LIKE ? OR nome LIKE ? OR cognome LIKE ?)
        ");
        $stmt->execute([$like, $like, $like, $like]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM utenti WHERE ruolo IN ('user', 'admin')");
    }
    return (int) $stmt->fetchColumn();
}

function getAllProfessionals($limit = 50, $offset = 0, string $search = '')
{
    global $pdo;
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $pdo->prepare("
            SELECT id, username, email, nome, cognome, ragione_sociale,
                   partita_iva, tipo_attivita, sconto_percentuale, citta, creato_il
            FROM utenti
            WHERE ruolo = 'professional'
              AND (ragione_sociale LIKE ? OR email LIKE ? OR nome LIKE ? OR cognome LIKE ? OR citta LIKE ? OR partita_iva LIKE ?)
            ORDER BY creato_il DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$like, $like, $like, $like, $like, $like, $limit, $offset]);
    } else {
        $stmt = $pdo->prepare("
            SELECT id, username, email, nome, cognome, ragione_sociale,
                   partita_iva, tipo_attivita, sconto_percentuale, citta, creato_il
            FROM utenti
            WHERE ruolo = 'professional'
            ORDER BY creato_il DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
    }
    return $stmt->fetchAll();
}

function countAllProfessionals(string $search = ''): int
{
    global $pdo;
    if ($search !== '') {
        $like = '%' . $search . '%';
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM utenti
            WHERE ruolo = 'professional'
              AND (ragione_sociale LIKE ? OR email LIKE ? OR nome LIKE ? OR cognome LIKE ? OR citta LIKE ? OR partita_iva LIKE ?)
        ");
        $stmt->execute([$like, $like, $like, $like, $like, $like]);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM utenti WHERE ruolo = 'professional'");
    }
    return (int) $stmt->fetchColumn();
}

function getUserById($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function deleteUser($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
    return $stmt->execute([$userId]);
}

function updateUserRole($userId, $ruolo)
{
    global $pdo;
    if (!in_array($ruolo, ['user', 'admin'])) {
        throw new Exception("Ruolo non valido");
    }
    $stmt = $pdo->prepare("UPDATE utenti SET ruolo = ? WHERE id = ?");
    return $stmt->execute([$ruolo, $userId]);
}

function updateProfessionalDiscount(int $userId, float $sconto): void
{
    global $pdo;
    if ($sconto < 0 || $sconto > 100) {
        throw new Exception("Percentuale di sconto non valida (0–100)");
    }
    $stmt = $pdo->prepare("UPDATE utenti SET sconto_percentuale = ? WHERE id = ? AND ruolo = 'professional'");
    $stmt->execute([$sconto, $userId]);
    if ($stmt->rowCount() === 0) {
        throw new Exception("Utente non trovato o non è un professionista");
    }
}

function getUserStats()
{
    global $pdo;
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as totale_utenti,
            COUNT(CASE WHEN ruolo = 'user' THEN 1 END) as totale_clienti,
            COUNT(CASE WHEN ruolo = 'professional' THEN 1 END) as totale_professionisti,
            COUNT(CASE WHEN ruolo = 'admin' THEN 1 END) as totale_admin,
            COUNT(CASE WHEN DATE(creato_il) = CURDATE() THEN 1 END) as nuovi_oggi
        FROM utenti
    ");
    return $stmt->fetch();
}
