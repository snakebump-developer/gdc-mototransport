<?php
require_once __DIR__ . '/db.php';

function getAllUsers($limit = 50, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT id, username, email, nome, cognome, ruolo, creato_il 
        FROM utenti 
        ORDER BY creato_il DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

function getUserById($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function deleteUser($userId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM utenti WHERE id = ?");
    return $stmt->execute([$userId]);
}

function updateUserRole($userId, $ruolo) {
    global $pdo;
    if (!in_array($ruolo, ['user', 'admin'])) {
        throw new Exception("Ruolo non valido");
    }
    $stmt = $pdo->prepare("UPDATE utenti SET ruolo = ? WHERE id = ?");
    return $stmt->execute([$ruolo, $userId]);
}

function getUserStats() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as totale_utenti,
            COUNT(CASE WHEN ruolo = 'admin' THEN 1 END) as totale_admin,
            COUNT(CASE WHEN DATE(creato_il) = DATE('now') THEN 1 END) as nuovi_oggi
        FROM utenti
    ");
    return $stmt->fetch();
}
