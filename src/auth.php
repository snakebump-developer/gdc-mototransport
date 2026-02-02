<?php
require_once __DIR__ . '/db.php';

// Validazione email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validazione username (solo alfanumerici e underscore, 3-20 caratteri)
function validateUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Validazione password (minimo 8 caratteri, almeno 1 lettera, 1 numero)
function validatePassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Za-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

function registerUser($username, $email, $password) {
    global $pdo;
    
    // Validazioni
    if (!validateUsername($username)) {
        throw new Exception("Username non valido. Usa 3-20 caratteri alfanumerici.");
    }
    if (!validateEmail($email)) {
        throw new Exception("Email non valida.");
    }
    if (!validatePassword($password)) {
        throw new Exception("Password deve avere almeno 8 caratteri, una lettera e un numero.");
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO utenti (username, email, password) VALUES (?, ?, ?)");
    
    try {
        return $stmt->execute([$username, $email, $hash]);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'UNIQUE constraint') !== false) {
            throw new Exception("Username o Email già esistenti.");
        }
        throw $e;
    }
}

function loginUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['ruolo'] = $user['ruolo'];
        return true;
    }
    return false;
}

function isLogged() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin';
}

function requireLogin() {
    if (!isLogged()) {
        header('Location: /login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /index.php');
        exit;
    }
}

function getCurrentUser() {
    global $pdo;
    if (!isLogged()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function updateUserProfile($userId, $data) {
    global $pdo;
    
    $allowed = ['nome', 'cognome', 'telefono', 'indirizzo', 'citta', 'cap', 'paese'];
    $fields = [];
    $values = [];
    
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    
    if (empty($fields)) return false;
    
    $values[] = $userId;
    $sql = "UPDATE utenti SET " . implode(', ', $fields) . ", aggiornato_il = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}