<?php
$config = require __DIR__ . '/config.php';
if (!file_exists($config['db_dir'])) mkdir($config['db_dir'], 0755, true);

$dbPath = $config['db_dir'] . '/' . $config['db_name'];

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA foreign_keys = ON");
    $pdo->exec("PRAGMA journal_mode = WAL");
} catch (PDOException $e) {
    die("Errore critico del database: " . $e->getMessage() . "\n");
}

// =========================================================
// TABELLA UTENTI (user | professional | admin)
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS utenti (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    username                TEXT    UNIQUE NOT NULL,
    email                   TEXT    UNIQUE NOT NULL,
    password                TEXT    NOT NULL,
    nome                    TEXT,
    cognome                 TEXT,
    telefono                TEXT,
    indirizzo               TEXT,
    citta                   TEXT,
    cap                     TEXT,
    paese                   TEXT    DEFAULT 'Italia',
    ruolo                   TEXT    DEFAULT 'user'
                                    CHECK(ruolo IN ('user','professional','admin')),
    -- campi esclusivi professionisti
    ragione_sociale         TEXT,
    partita_iva             TEXT    UNIQUE,
    codice_fiscale_azienda  TEXT,
    pec                     TEXT,
    codice_sdi              TEXT,
    tipo_attivita           TEXT,
    sconto_percentuale      REAL    DEFAULT 10.00,
    indirizzo_fatturazione  TEXT,
    citta_fatturazione      TEXT,
    cap_fatturazione        TEXT,
    -- GDPR
    gdpr_accettato          INTEGER DEFAULT 0,
    gdpr_accettato_il       DATETIME,
    marketing_accettato     INTEGER DEFAULT 0,
    -- timestamp
    creato_il               DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il           DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// =========================================================
// TABELLA ORDINI
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS ordini (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id           INTEGER NOT NULL,
    totale            REAL    NOT NULL,
    stato             TEXT    DEFAULT 'pending'
                              CHECK(stato IN ('pending','processing','completed','cancelled')),
    metodo_pagamento  TEXT,
    transaction_id    TEXT,
    note              TEXT,
    creato_il         DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
)");

// =========================================================
// TABELLA DETTAGLI ORDINI
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS ordini_dettagli (
    id               INTEGER PRIMARY KEY AUTOINCREMENT,
    ordine_id        INTEGER NOT NULL,
    descrizione      TEXT    NOT NULL,
    quantita         INTEGER DEFAULT 1,
    prezzo_unitario  REAL    NOT NULL,
    FOREIGN KEY (ordine_id) REFERENCES ordini(id) ON DELETE CASCADE
)");

// =========================================================
// TABELLA MOTO SALVATE (utenti normali e professionisti)
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS moto_salvate (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL,
    marca       TEXT    NOT NULL,
    modello     TEXT    NOT NULL,
    anno        INTEGER,
    cilindrata  INTEGER,
    targa       TEXT,
    colore      TEXT,
    note        TEXT,
    creato_il   DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
)");

// =========================================================
// TABELLA PREVENTIVI
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS preventivi (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id             INTEGER,
    nome_cliente        TEXT,
    email_cliente       TEXT,
    telefono_cliente    TEXT,
    indirizzo_ritiro    TEXT    NOT NULL,
    indirizzo_consegna  TEXT    NOT NULL,
    distanza_km         REAL,
    marca_moto          TEXT,
    modello_moto        TEXT,
    anno_moto           INTEGER,
    cilindrata          INTEGER,
    targa               TEXT,
    prezzo_base         REAL,
    sconto_applicato    REAL    DEFAULT 0,
    prezzo_finale       REAL,
    stato               TEXT    DEFAULT 'bozza'
                                CHECK(stato IN ('bozza','inviato','confermato',
                                                'in_lavorazione','completato','annullato')),
    note                TEXT,
    creato_il           DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL
)");

// =========================================================
// MIGRAZIONI: aggiunge colonne mancanti su DB esistenti
// =========================================================
function columnExists(PDO $db, string $table, string $column): bool
{
    $info = $db->query("PRAGMA table_info($table)")->fetchAll();
    foreach ($info as $col) {
        if ($col['name'] === $column) return true;
    }
    return false;
}

$migrations = [
    'utenti' => [
        'ruolo'                  => "TEXT DEFAULT 'user'",
        'ragione_sociale'        => 'TEXT',
        'partita_iva'            => 'TEXT',
        'codice_fiscale_azienda' => 'TEXT',
        'pec'                    => 'TEXT',
        'codice_sdi'             => 'TEXT',
        'tipo_attivita'          => 'TEXT',
        'sconto_percentuale'     => 'REAL DEFAULT 10.00',
        'indirizzo_fatturazione' => 'TEXT',
        'citta_fatturazione'     => 'TEXT',
        'cap_fatturazione'       => 'TEXT',
        'gdpr_accettato'         => 'INTEGER DEFAULT 0',
        'gdpr_accettato_il'      => 'DATETIME',
        'marketing_accettato'    => 'INTEGER DEFAULT 0',
        'paese'                  => "TEXT DEFAULT 'Italia'",
        'avatar'                 => 'TEXT',
    ],
    'preventivi' => [
        'data_ritiro'             => 'DATE',
        'tipo_consegna'           => "TEXT DEFAULT 'Standard'",
        'codice_fiscale_cliente'  => 'TEXT',
        'borse_laterali'          => 'REAL DEFAULT 0',
    ],
];

foreach ($migrations as $table => $columns) {
    foreach ($columns as $col => $type) {
        if (!columnExists($pdo, $table, $col)) {
            $pdo->exec("ALTER TABLE $table ADD COLUMN $col $type");
            echo "  Migrazione: colonna '$col' aggiunta a '$table'\n";
        }
    }
}

// =========================================================
// ADMIN DI DEFAULT
// Credenziali: username=admin_gdc | password=GDC@Admin2024!
// =========================================================
try {
    $check = $pdo->query("SELECT COUNT(*) AS cnt FROM utenti WHERE ruolo='admin'")->fetch();
    if ((int)$check['cnt'] === 0) {
        $hash = password_hash('GDC@Admin2024!', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO utenti (username, email, password, nome, cognome, ruolo, gdpr_accettato)
            VALUES (?, ?, ?, ?, ?, 'admin', 1)
        ");
        $stmt->execute(['admin_gdc', 'admin@gdcmototransport.it', $hash, 'Admin', 'GDC']);
        echo "Admin creato!\n";
        echo "  Username : admin_gdc\n";
        echo "  Password : GDC\@Admin2024!\n";
        echo "  Email    : admin\@gdcmototransport.it\n";
        echo "  ⚠️  Cambia la password al primo accesso!\n";
    }
} catch (Exception $e) {
    // admin già presente
}

echo "\nDatabase pronto ✓ (tabelle: utenti, ordini, ordini_dettagli, moto_salvate, preventivi)\n";
