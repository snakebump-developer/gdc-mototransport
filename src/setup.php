<?php
$config = require __DIR__ . '/config.php';
if (!file_exists($config['db_dir'])) mkdir($config['db_dir'], 0777, true);

require __DIR__ . '/db.php';

// Tabella utenti con ruoli
$sqlUtenti = "CREATE TABLE IF NOT EXISTS utenti (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    nome TEXT,
    cognome TEXT,
    telefono TEXT,
    indirizzo TEXT,
    citta TEXT,
    cap TEXT,
    paese TEXT,
    ruolo TEXT DEFAULT 'user', -- 'user' o 'admin'
    creato_il DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il DATETIME DEFAULT CURRENT_TIMESTAMP
)";

// Tabella ordini
$sqlOrdini = "CREATE TABLE IF NOT EXISTS ordini (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    totale DECIMAL(10,2) NOT NULL,
    stato TEXT DEFAULT 'pending', -- pending, processing, completed, cancelled
    metodo_pagamento TEXT, -- stripe, paypal
    transaction_id TEXT,
    note TEXT,
    creato_il DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
)";

// Tabella dettagli ordini (per ordini con più item)
$sqlDettagliOrdini = "CREATE TABLE IF NOT EXISTS ordini_dettagli (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ordine_id INTEGER NOT NULL,
    descrizione TEXT NOT NULL,
    quantita INTEGER DEFAULT 1,
    prezzo_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (ordine_id) REFERENCES ordini(id) ON DELETE CASCADE
)";

$pdo->exec($sqlUtenti);
$pdo->exec($sqlOrdini);
$pdo->exec($sqlDettagliOrdini);

// Crea un admin di default se non esiste
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utenti WHERE ruolo = 'admin'");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utenti (username, email, password, ruolo) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $adminPassword, 'admin']);
        echo "Admin predefinito creato! (username: admin, password: admin123)\n";
    }
} catch (Exception $e) {
    // Admin già esistente
}

echo "Database pronto con tabelle: utenti, ordini, ordini_dettagli!\n";