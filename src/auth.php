<?php
require_once __DIR__ . '/db.php';

// Validazione email
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validazione username (solo alfanumerici e underscore, 3-20 caratteri)
function validateUsername($username)
{
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

// Validazione password (minimo 8 caratteri, almeno 1 lettera, 1 numero)
function validatePassword($password)
{
    return strlen($password) >= 8 &&
        preg_match('/[A-Za-z]/', $password) &&
        preg_match('/[0-9]/', $password);
}

// Validazione Partita IVA italiana (11 cifre con check digit Luhn)
function validatePartitaIVA(string $piva): bool
{
    $piva = preg_replace('/[\s\-]/', '', $piva);
    if (!preg_match('/^\d{11}$/', $piva)) return false;
    $sum = 0;
    for ($i = 0; $i < 10; $i++) {
        $d = (int)$piva[$i];
        if ($i % 2 === 0) {
            $sum += $d;
        } else {
            $d *= 2;
            $sum += ($d > 9) ? $d - 9 : $d;
        }
    }
    $check = (10 - ($sum % 10)) % 10;
    return $check === (int)$piva[10];
}

function registerUser($username, $email, $password, string $tipo = 'privato', array $extra = [])
{
    global $pdo;

    // Validazioni base
    if (!validateUsername($username)) {
        throw new Exception("Username non valido. Usa 3-20 caratteri alfanumerici.");
    }
    if (!validateEmail($email)) {
        throw new Exception("Email non valida.");
    }
    if (!validatePassword($password)) {
        throw new Exception("Password deve avere almeno 8 caratteri, una lettera e un numero.");
    }

    // GDPR obbligatorio
    if (empty($extra['gdpr_accettato'])) {
        throw new Exception("Devi accettare la Privacy Policy per registrarti.");
    }

    $ruolo = ($tipo === 'professional') ? 'professional' : 'user';

    // Validazioni aggiuntive per professionisti
    if ($ruolo === 'professional') {
        if (empty(trim($extra['ragione_sociale'] ?? ''))) {
            throw new Exception("La ragione sociale è obbligatoria.");
        }
        if (empty(trim($extra['partita_iva'] ?? ''))) {
            throw new Exception("La Partita IVA è obbligatoria.");
        }
        if (!validatePartitaIVA($extra['partita_iva'])) {
            throw new Exception("Partita IVA non valida (deve essere un numero italiano a 11 cifre valido).");
        }
        if (!empty($extra['pec']) && !validateEmail($extra['pec'])) {
            throw new Exception("Indirizzo PEC non valido.");
        }
    }

    $hash    = password_hash($password, PASSWORD_DEFAULT);
    $gdprAt  = !empty($extra['gdpr_accettato']) ? date('Y-m-d H:i:s') : null;
    $pivaVal = ($ruolo === 'professional') ? preg_replace('/[\s\-]/', '', $extra['partita_iva'] ?? '') : null;

    $stmt = $pdo->prepare("
        INSERT INTO utenti (
            username, email, password, ruolo,
            nome, cognome, telefono,
            ragione_sociale, partita_iva, codice_fiscale_azienda,
            pec, codice_sdi, tipo_attivita,
            gdpr_accettato, gdpr_accettato_il, marketing_accettato
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?
        )
    ");

    try {
        return $stmt->execute([
            $username,
            $email,
            $hash,
            $ruolo,
            $extra['nome']    ?? null,
            $extra['cognome'] ?? null,
            $extra['telefono'] ?? null,
            ($ruolo === 'professional') ? trim($extra['ragione_sociale'] ?? '') : null,
            $pivaVal,
            !empty($extra['codice_fiscale_azienda']) ? strtoupper(trim($extra['codice_fiscale_azienda'])) : null,
            !empty($extra['pec'])        ? trim($extra['pec'])        : null,
            !empty($extra['codice_sdi']) ? strtoupper(trim($extra['codice_sdi'])) : null,
            !empty($extra['tipo_attivita']) ? $extra['tipo_attivita'] : null,
            $extra['gdpr_accettato'] ? 1 : 0,
            $gdprAt,
            $extra['marketing_accettato'] ? 1 : 0,
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            if (strpos($e->getMessage(), 'partita_iva') !== false) {
                throw new Exception("Partita IVA già registrata nel sistema.");
            }
            throw new Exception("Username o Email già esistenti.");
        }
        throw $e;
    }
}

function loginUser($username, $password)
{
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

function isLogged()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin';
}

function requireLogin()
{
    if (!isLogged()) {
        header('Location: /login');
        exit;
    }
}

function requireAdmin()
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: /');
        exit;
    }
}

function getCurrentUser()
{
    global $pdo;
    if (!isLogged()) return null;

    $stmt = $pdo->prepare("SELECT * FROM utenti WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function isProfessional()
{
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'professional';
}

function getDashboardUrl()
{
    if (isAdmin()) return '/admin';
    if (isProfessional()) return '/dashboard/pro';
    return '/dashboard';
}

function updateUserProfile($userId, $data)
{
    global $pdo;

    $allowed = ['nome', 'cognome', 'telefono', 'indirizzo', 'citta', 'cap', 'paese', 'codice_fiscale_azienda'];
    $fields = [];
    $values = [];

    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $val = $data[$field];
            if ($field === 'codice_fiscale_azienda' && $val !== '') {
                $val = strtoupper(trim($val));
                if (!preg_match('/^[A-Z0-9]{11,16}$/', $val)) {
                    throw new Exception("Codice fiscale non valido.");
                }
            }
            $fields[] = "$field = ?";
            $values[] = $val;
        }
    }

    if (empty($fields)) return false;

    $values[] = $userId;
    $sql = "UPDATE utenti SET " . implode(', ', $fields) . ", aggiornato_il = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($values);
}

function updateProfessionalProfile($userId, $data)
{
    global $pdo;

    $allowed = [
        'nome',
        'cognome',
        'telefono',
        'pec',
        'codice_sdi',
        'indirizzo_fatturazione',
        'citta_fatturazione',
        'cap_fatturazione',
    ];
    $fields = [];
    $values = [];

    foreach ($allowed as $field) {
        if (array_key_exists($field, $data)) {
            $val = trim((string)$data[$field]);
            if ($field === 'pec' && $val !== '' && !validateEmail($val)) {
                throw new Exception("Indirizzo PEC non valido.");
            }
            if ($field === 'codice_sdi' && $val !== '' && !preg_match('/^[A-Z0-9]{6,7}$/i', $val)) {
                throw new Exception("Codice SDI non valido (6-7 caratteri alfanumerici).");
            }
            $fields[] = "$field = ?";
            $values[] = $val !== '' ? $val : null;
        }
    }

    if (empty($fields)) return false;

    $values[] = $userId;
    $sql = "UPDATE utenti SET " . implode(', ', $fields) . ", aggiornato_il = CURRENT_TIMESTAMP WHERE id = ?";
    return $pdo->prepare($sql)->execute($values);
}

function updateUserAvatar($userId, $avatarPath)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE utenti SET avatar = ?, aggiornato_il = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$avatarPath, $userId]);
}

function removeUserAvatar($userId)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE utenti SET avatar = NULL, aggiornato_il = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$userId]);
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): void
{
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        throw new Exception("Token di sicurezza non valido. Ricarica la pagina e riprova.");
    }
}
