<?php
$config = require __DIR__ . '/config.php';
$dbConf = $config['db'];

try {
    $dsn = "mysql:host={$dbConf['host']};port={$dbConf['port']};dbname={$dbConf['name']};charset={$dbConf['charset']}";
    $pdo = new PDO($dsn, $dbConf['user'], $dbConf['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Errore critico del database: " . $e->getMessage() . "\n");
}

echo "Connessione a MySQL riuscita ({$dbConf['name']})\n";

// =========================================================
// TABELLA UTENTI (user | professional | admin)
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS utenti (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    username                VARCHAR(100) UNIQUE NOT NULL,
    email                   VARCHAR(255) UNIQUE NOT NULL,
    password                VARCHAR(255) NOT NULL,
    nome                    VARCHAR(100),
    cognome                 VARCHAR(100),
    telefono                VARCHAR(30),
    indirizzo               VARCHAR(255),
    citta                   VARCHAR(100),
    cap                     VARCHAR(10),
    paese                   VARCHAR(100) DEFAULT 'Italia',
    ruolo                   ENUM('user','professional','admin') DEFAULT 'user',
    -- campi esclusivi professionisti
    ragione_sociale         VARCHAR(255),
    partita_iva             VARCHAR(20) UNIQUE,
    codice_fiscale_azienda  VARCHAR(20),
    pec                     VARCHAR(255),
    codice_sdi              VARCHAR(10),
    tipo_attivita           VARCHAR(255),
    sconto_percentuale      DECIMAL(5,2) DEFAULT 10.00,
    indirizzo_fatturazione  VARCHAR(255),
    citta_fatturazione      VARCHAR(100),
    cap_fatturazione        VARCHAR(10),
    -- GDPR
    gdpr_accettato          TINYINT DEFAULT 0,
    gdpr_accettato_il       DATETIME,
    marketing_accettato     TINYINT DEFAULT 0,
    -- avatar
    avatar                  VARCHAR(255),
    -- timestamp
    creato_il               DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il           DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// TABELLA ORDINI
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS ordini (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    user_id           INT NOT NULL,
    totale            DECIMAL(10,2) NOT NULL,
    stato             ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
    metodo_pagamento  VARCHAR(50),
    transaction_id    VARCHAR(255),
    note              TEXT,
    creato_il         DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// TABELLA DETTAGLI ORDINI
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS ordini_dettagli (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    ordine_id        INT NOT NULL,
    descrizione      VARCHAR(500) NOT NULL,
    quantita         INT DEFAULT 1,
    prezzo_unitario  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (ordine_id) REFERENCES ordini(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// TABELLA MOTO SALVATE (utenti normali e professionisti)
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS moto_salvate (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    marca         VARCHAR(100) NOT NULL,
    modello       VARCHAR(100) NOT NULL,
    anno          INT,
    cilindrata    INT,
    targa         VARCHAR(20),
    colore        VARCHAR(50),
    note          TEXT,
    creato_il     DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// TABELLA PREVENTIVI
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS preventivi (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    user_id                  INT,
    nome_cliente             VARCHAR(200),
    email_cliente            VARCHAR(255),
    telefono_cliente         VARCHAR(30),
    codice_fiscale_cliente   VARCHAR(20),
    indirizzo_ritiro         VARCHAR(500) NOT NULL,
    indirizzo_consegna       VARCHAR(500) NOT NULL,
    distanza_km              DECIMAL(10,2),
    marca_moto               VARCHAR(100),
    modello_moto             VARCHAR(100),
    anno_moto                INT,
    cilindrata               INT,
    targa                    VARCHAR(20),
    borse_laterali           DECIMAL(10,2) DEFAULT 0,
    tipo_consegna            VARCHAR(50) DEFAULT 'Standard',
    data_ritiro              DATE,
    prezzo_base              DECIMAL(10,2),
    sconto_applicato         DECIMAL(10,2) DEFAULT 0,
    prezzo_finale            DECIMAL(10,2),
    stato                    ENUM('bozza','inviato','confermato','in_lavorazione','completato','annullato') DEFAULT 'bozza',
    note                     TEXT,
    stripe_payment_intent_id VARCHAR(255),
    pagamento_stato          VARCHAR(50) DEFAULT 'non_pagato',
    pagamento_id             INT,
    scadenza_il              DATETIME,
    route_data_json          JSON,
    creato_il                DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il            DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// TABELLA PAGAMENTI (legata ai preventivi)
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS pagamenti (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    preventivo_id            INT NOT NULL,
    stripe_payment_intent_id VARCHAR(255) UNIQUE,
    importo                  DECIMAL(10,2) NOT NULL,
    valuta                   VARCHAR(5) DEFAULT 'eur',
    stato                    ENUM('pending','paid','failed','refunded','cancelled') DEFAULT 'pending',
    stripe_metodo            VARCHAR(50),
    stripe_ultimi_4          VARCHAR(4),
    stripe_brand             VARCHAR(30),
    stripe_receipt_url       VARCHAR(500),
    creato_il                DATETIME DEFAULT CURRENT_TIMESTAMP,
    aggiornato_il            DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (preventivo_id) REFERENCES preventivi(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// CATALOGO MOTO UFFICIALE
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS catalogo_moto (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    marca   VARCHAR(100) NOT NULL,
    modello VARCHAR(100) NOT NULL,
    UNIQUE KEY uk_marca_modello (marca, modello)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// MOTO IN BOZZA (proposte utenti, in attesa di approvazione admin)
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS moto_bozze (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    marca      VARCHAR(100) NOT NULL,
    modello    VARCHAR(100) NOT NULL,
    stato      ENUM('in_attesa','approvata','rifiutata') DEFAULT 'in_attesa',
    note_admin TEXT,
    creato_il  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_bozza_marca_modello (marca, modello)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// =========================================================
// IMPOSTAZIONI APPLICAZIONE
// =========================================================
$pdo->exec("CREATE TABLE IF NOT EXISTS app_settings (
    setting_key   VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Valore iniziale: manutenzione disattivata
$pdo->exec("INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES ('maintenance_mode', '0')");

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
        echo "  Password : GDC@Admin2024!\n";
        echo "  Email    : admin@gdcmototransport.it\n";
        echo "  ⚠️  Cambia la password al primo accesso!\n";
    }
} catch (Exception $e) {
    // admin già presente
}

echo "\nDatabase MySQL pronto ✓ (tabelle: utenti, ordini, ordini_dettagli, moto_salvate, preventivi, pagamenti, catalogo_moto, moto_bozze)\n";
