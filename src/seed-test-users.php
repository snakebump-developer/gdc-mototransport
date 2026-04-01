<?php

/**
 * Seed utenti di test — esegui con:  php src/seed-test-users.php
 *
 * Crea 5 utenti normali e 5 professionisti fittizi.
 * Gli utenti esistenti (per username/email) vengono saltati.
 */

$config = require __DIR__ . '/config.php';
$dbPath = $config['db_dir'] . '/' . $config['db_name'];

if (!file_exists($dbPath)) {
    die("❌ Database non trovato. Esegui prima: php src/setup.php\n");
}

$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// =========================================================
// UTENTI NORMALI (ruolo = 'user')
// ⭐ Credenziali di test riportate nel README:
//    marco.rossi92 / Test@User1
// =========================================================
$utentiNormali = [
    [
        'username'           => 'marco.rossi92',
        'email'              => 'marco.rossi92@email.it',
        'password'           => 'Test@User1',          // ⭐ credenziali di test
        'nome'               => 'Marco',
        'cognome'            => 'Rossi',
        'telefono'           => '3381234567',
        'indirizzo'          => 'Via Roma 14',
        'citta'              => 'Milano',
        'cap'                => '20121',
        'paese'              => 'Italia',
        'gdpr_accettato'     => 1,
        'marketing_accettato' => 1,
    ],
    [
        'username'           => 'giulia.ferrari',
        'email'              => 'giulia.ferrari@email.it',
        'password'           => 'Giulia#2025',
        'nome'               => 'Giulia',
        'cognome'            => 'Ferrari',
        'telefono'           => '3479876543',
        'indirizzo'          => 'Corso Buenos Aires 55',
        'citta'              => 'Milano',
        'cap'                => '20124',
        'paese'              => 'Italia',
        'gdpr_accettato'     => 1,
        'marketing_accettato' => 0,
    ],
    [
        'username'           => 'luca.bianchi',
        'email'              => 'luca.bianchi@email.it',
        'password'           => 'Bianchi!88',
        'nome'               => 'Luca',
        'cognome'            => 'Bianchi',
        'telefono'           => '3356781234',
        'indirizzo'          => 'Via Garibaldi 7',
        'citta'              => 'Torino',
        'cap'                => '10122',
        'paese'              => 'Italia',
        'gdpr_accettato'     => 1,
        'marketing_accettato' => 1,
    ],
    [
        'username'           => 'sara.colombo',
        'email'              => 'sara.colombo@email.it',
        'password'           => 'Sara@Moto99',
        'nome'               => 'Sara',
        'cognome'            => 'Colombo',
        'telefono'           => '3204567890',
        'indirizzo'          => 'Piazza Dante 3',
        'citta'              => 'Bologna',
        'cap'                => '40125',
        'paese'              => 'Italia',
        'gdpr_accettato'     => 1,
        'marketing_accettato' => 0,
    ],
    [
        'username'           => 'andrea.martini',
        'email'              => 'andrea.martini@email.it',
        'password'           => 'Martini#77',
        'nome'               => 'Andrea',
        'cognome'            => 'Martini',
        'telefono'           => '3123456789',
        'indirizzo'          => 'Via Napoli 22',
        'citta'              => 'Roma',
        'cap'                => '00185',
        'paese'              => 'Italia',
        'gdpr_accettato'     => 1,
        'marketing_accettato' => 1,
    ],
];

// =========================================================
// UTENTI PROFESSIONISTI (ruolo = 'professional')
// ⭐ Credenziali di test riportate nel README:
//    trasporti.esposito / Pro@Moto2025
// =========================================================
$utentiProfessionisti = [
    [
        'username'               => 'trasporti.esposito',
        'email'                  => 'info@trasportiesposito.it',
        'password'               => 'Pro@Moto2025',        // ⭐ credenziali di test
        'nome'                   => 'Antonio',
        'cognome'                => 'Esposito',
        'telefono'               => '0815561234',
        'indirizzo'              => 'Via Caracciolo 88',
        'citta'                  => 'Napoli',
        'cap'                    => '80122',
        'paese'                  => 'Italia',
        'ragione_sociale'        => 'Trasporti Esposito S.r.l.',
        'partita_iva'            => 'IT08765432100',
        'codice_fiscale_azienda' => '08765432100',
        'pec'                    => 'trasportiesposito@pec.it',
        'codice_sdi'             => 'ESDMTO1',
        'tipo_attivita'          => 'Trasporto veicoli su gomma',
        'sconto_percentuale'     => 12.00,
        'indirizzo_fatturazione' => 'Via Caracciolo 88',
        'citta_fatturazione'     => 'Napoli',
        'cap_fatturazione'       => '80122',
        'gdpr_accettato'         => 1,
        'marketing_accettato'    => 1,
    ],
    [
        'username'               => 'motoservice.milano',
        'email'              => 'info@motoservicemilano.it',
        'password'               => 'MsrMi!2025',
        'nome'                   => 'Roberto',
        'cognome'                => 'Riccardi',
        'telefono'               => '0236985412',
        'indirizzo'              => 'Via Arona 15',
        'citta'                  => 'Milano',
        'cap'                    => '20149',
        'paese'                  => 'Italia',
        'ragione_sociale'        => 'MotoService Milano S.n.c.',
        'partita_iva'            => 'IT02345678901',
        'codice_fiscale_azienda' => '02345678901',
        'pec'                    => 'motoservicemi@pec.it',
        'codice_sdi'             => 'MSMIL01',
        'tipo_attivita'          => 'Officina e trasporto moto',
        'sconto_percentuale'     => 15.00,
        'indirizzo_fatturazione' => 'Via Arona 15',
        'citta_fatturazione'     => 'Milano',
        'cap_fatturazione'       => '20149',
        'gdpr_accettato'         => 1,
        'marketing_accettato'    => 1,
    ],
    [
        'username'               => 'garage.ricci',
        'email'              => 'info@garagericci.it',
        'password'               => 'GrgRcc#33',
        'nome'                   => 'Simone',
        'cognome'                => 'Ricci',
        'telefono'               => '0556712345',
        'indirizzo'              => 'Via Senese 101',
        'citta'                  => 'Firenze',
        'cap'                    => '50124',
        'paese'                  => 'Italia',
        'ragione_sociale'        => 'Garage Ricci di Simone Ricci',
        'partita_iva'            => 'IT05678901234',
        'codice_fiscale_azienda' => '05678901234',
        'pec'                    => 'garagericci@pec.it',
        'codice_sdi'             => 'GRFI001',
        'tipo_attivita'          => 'Concessionaria e logistica moto',
        'sconto_percentuale'     => 10.00,
        'indirizzo_fatturazione' => 'Via Senese 101',
        'citta_fatturazione'     => 'Firenze',
        'cap_fatturazione'       => '50124',
        'gdpr_accettato'         => 1,
        'marketing_accettato'    => 0,
    ],
    [
        'username'               => 'motoexpress.romano',
        'email'              => 'info@motoexpressromano.it',
        'password'               => 'Xpress!Rm2',
        'nome'                   => 'Davide',
        'cognome'                => 'Romano',
        'telefono'               => '0669871234',
        'indirizzo'              => 'Viale Trastevere 45',
        'citta'                  => 'Roma',
        'cap'                    => '00153',
        'paese'                  => 'Italia',
        'ragione_sociale'        => 'MotoExpress Romano S.r.l.',
        'partita_iva'            => 'IT07890123456',
        'codice_fiscale_azienda' => '07890123456',
        'pec'                    => 'motoexpressromano@pec.it',
        'codice_sdi'             => 'MXRM007',
        'tipo_attivita'          => 'Spedizioni e trasporto moto nazionali',
        'sconto_percentuale'     => 18.00,
        'indirizzo_fatturazione' => 'Viale Trastevere 45',
        'citta_fatturazione'     => 'Roma',
        'cap_fatturazione'       => '00153',
        'gdpr_accettato'         => 1,
        'marketing_accettato'    => 1,
    ],
    [
        'username'               => 'sportbike.conti',
        'email'              => 'info@sportbikeconti.it',
        'password'               => 'Sp0rtC!2025',
        'nome'                   => 'Federico',
        'cognome'                => 'Conti',
        'telefono'               => '0516543210',
        'indirizzo'              => 'Via Rizzoli 9',
        'citta'                  => 'Bologna',
        'cap'                    => '40125',
        'paese'                  => 'Italia',
        'ragione_sociale'        => 'Sport Bike Conti S.r.l.',
        'partita_iva'            => 'IT09012345678',
        'codice_fiscale_azienda' => '09012345678',
        'pec'                    => 'sportbikeconti@pec.it',
        'codice_sdi'             => 'SBCBO01',
        'tipo_attivita'          => 'Vendita e trasporto moto sportive',
        'sconto_percentuale'     => 20.00,
        'indirizzo_fatturazione' => 'Via Rizzoli 9',
        'citta_fatturazione'     => 'Bologna',
        'cap_fatturazione'       => '40125',
        'gdpr_accettato'         => 1,
        'marketing_accettato'    => 1,
    ],
];

// =========================================================
// INSERIMENTO UTENTI NORMALI
// =========================================================
$stmtUser = $pdo->prepare("
    INSERT OR IGNORE INTO utenti
        (username, email, password, nome, cognome, telefono,
         indirizzo, citta, cap, paese, ruolo,
         gdpr_accettato, gdpr_accettato_il, marketing_accettato)
    VALUES
        (:username, :email, :password, :nome, :cognome, :telefono,
         :indirizzo, :citta, :cap, :paese, 'user',
         :gdpr_accettato, CURRENT_TIMESTAMP, :marketing_accettato)
");

echo "\n--- Utenti normali ---\n";
foreach ($utentiNormali as $u) {
    $u['password'] = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmtUser->execute($u);
    $affected = $stmtUser->rowCount();
    echo ($affected > 0 ? "  ✅ Inserito: " : "  ⚠️  Già presente: ") . $u['username'] . "\n";
}

// =========================================================
// INSERIMENTO PROFESSIONISTI
// =========================================================
$stmtPro = $pdo->prepare("
    INSERT OR IGNORE INTO utenti
        (username, email, password, nome, cognome, telefono,
         indirizzo, citta, cap, paese, ruolo,
         ragione_sociale, partita_iva, codice_fiscale_azienda, pec,
         codice_sdi, tipo_attivita, sconto_percentuale,
         indirizzo_fatturazione, citta_fatturazione, cap_fatturazione,
         gdpr_accettato, gdpr_accettato_il, marketing_accettato)
    VALUES
        (:username, :email, :password, :nome, :cognome, :telefono,
         :indirizzo, :citta, :cap, :paese, 'professional',
         :ragione_sociale, :partita_iva, :codice_fiscale_azienda, :pec,
         :codice_sdi, :tipo_attivita, :sconto_percentuale,
         :indirizzo_fatturazione, :citta_fatturazione, :cap_fatturazione,
         :gdpr_accettato, CURRENT_TIMESTAMP, :marketing_accettato)
");

echo "\n--- Utenti professionisti ---\n";
foreach ($utentiProfessionisti as $u) {
    $u['password'] = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmtPro->execute($u);
    $affected = $stmtPro->rowCount();
    echo ($affected > 0 ? "  ✅ Inserito: " : "  ⚠️  Già presente: ") . $u['username'] . "\n";
}

// =========================================================
// PREVENTIVI FITTIZI
// Collegati agli utenti di test (risolve user_id per username)
// =========================================================
$preventivi = [
    [
        // Preventivo 1 - utente normale marco.rossi92, stato: completato
        'username'           => 'marco.rossi92',
        'nome_cliente'       => 'Marco Rossi',
        'email_cliente'      => 'marco.rossi92@email.it',
        'telefono_cliente'   => '3381234567',
        'indirizzo_ritiro'   => 'Via Roma 14, 20121 Milano',
        'indirizzo_consegna' => 'Via Toledo 100, 80132 Napoli',
        'distanza_km'        => 784.0,
        'marca_moto'         => 'Honda',
        'modello_moto'       => 'CB650R',
        'anno_moto'          => 2021,
        'cilindrata'         => 649,
        'targa'              => 'EX123GH',
        'prezzo_base'        => 320.00,
        'sconto_applicato'   => 0.00,
        'prezzo_finale'      => 320.00,
        'stato'              => 'completato',
        'note'               => 'Consegna urgente richiesta entro venerdì.',
    ],
    [
        // Preventivo 2 - professionista trasporti.esposito, stato: confermato
        'username'           => 'trasporti.esposito',
        'nome_cliente'       => 'Trasporti Esposito S.r.l.',
        'email_cliente'      => 'info@trasportiesposito.it',
        'telefono_cliente'   => '0815561234',
        'indirizzo_ritiro'   => 'Via Caracciolo 88, 80122 Napoli',
        'indirizzo_consegna' => 'Via della Libertà 5, 90143 Palermo',
        'distanza_km'        => 392.5,
        'marca_moto'         => 'Ducati',
        'modello_moto'       => 'Monster 937',
        'anno_moto'          => 2023,
        'cilindrata'         => 937,
        'targa'              => 'NA456KL',
        'prezzo_base'        => 210.00,
        'sconto_applicato'   => 25.20,
        'prezzo_finale'      => 184.80,
        'stato'              => 'confermato',
        'note'               => 'Sconto 12% cliente professional applicato.',
    ],
    [
        // Preventivo 3 - utente normale giulia.ferrari, stato: inviato
        'username'           => 'giulia.ferrari',
        'nome_cliente'       => 'Giulia Ferrari',
        'email_cliente'      => 'giulia.ferrari@email.it',
        'telefono_cliente'   => '3479876543',
        'indirizzo_ritiro'   => 'Corso Buenos Aires 55, 20124 Milano',
        'indirizzo_consegna' => 'Via dell\'Arcivescovado 7, 10121 Torino',
        'distanza_km'        => 140.0,
        'marca_moto'         => 'Yamaha',
        'modello_moto'       => 'MT-07',
        'anno_moto'          => 2022,
        'cilindrata'         => 689,
        'targa'              => 'MI789PQ',
        'prezzo_base'        => 130.00,
        'sconto_applicato'   => 0.00,
        'prezzo_finale'      => 130.00,
        'stato'              => 'inviato',
        'note'               => 'Cliente richiede ritiro al pomeriggio.',
    ],
    [
        // Preventivo 4 - professionista motoservice.milano, stato: in_lavorazione
        'username'           => 'motoservice.milano',
        'nome_cliente'       => 'MotoService Milano S.n.c.',
        'email_cliente'      => 'info@motoservicemilano.it',
        'telefono_cliente'   => '0236985412',
        'indirizzo_ritiro'   => 'Via Arona 15, 20149 Milano',
        'indirizzo_consegna' => 'Via Emilia Est 210, 41121 Modena',
        'distanza_km'        => 190.0,
        'marca_moto'         => 'BMW',
        'modello_moto'       => 'R 1250 GS',
        'anno_moto'          => 2020,
        'cilindrata'         => 1254,
        'targa'              => 'MI321RS',
        'prezzo_base'        => 160.00,
        'sconto_applicato'   => 24.00,
        'prezzo_finale'      => 136.00,
        'stato'              => 'in_lavorazione',
        'note'               => 'Moto di alto valore, maneggiare con cura.',
    ],
    [
        // Preventivo 5 - utente normale luca.bianchi, stato: bozza
        'username'           => 'luca.bianchi',
        'nome_cliente'       => 'Luca Bianchi',
        'email_cliente'      => 'luca.bianchi@email.it',
        'telefono_cliente'   => '3356781234',
        'indirizzo_ritiro'   => 'Via Garibaldi 7, 10122 Torino',
        'indirizzo_consegna' => 'Via dei Mille 30, 16121 Genova',
        'distanza_km'        => 168.0,
        'marca_moto'         => 'Kawasaki',
        'modello_moto'       => 'Z900',
        'anno_moto'          => 2019,
        'cilindrata'         => 948,
        'targa'              => 'TO654UV',
        'prezzo_base'        => 145.00,
        'sconto_applicato'   => 0.00,
        'prezzo_finale'      => 145.00,
        'stato'              => 'bozza',
        'note'               => 'Da confermare data di ritiro.',
    ],
];

$stmtPreventivo = $pdo->prepare("
    INSERT INTO preventivi
        (user_id, nome_cliente, email_cliente, telefono_cliente,
         indirizzo_ritiro, indirizzo_consegna, distanza_km,
         marca_moto, modello_moto, anno_moto, cilindrata, targa,
         prezzo_base, sconto_applicato, prezzo_finale, stato, note)
    VALUES
        (:user_id, :nome_cliente, :email_cliente, :telefono_cliente,
         :indirizzo_ritiro, :indirizzo_consegna, :distanza_km,
         :marca_moto, :modello_moto, :anno_moto, :cilindrata, :targa,
         :prezzo_base, :sconto_applicato, :prezzo_finale, :stato, :note)
");

$stmtUserId = $pdo->prepare("SELECT id FROM utenti WHERE username = ?");

echo "\n--- Preventivi fittizi ---\n";
foreach ($preventivi as $p) {
    $stmtUserId->execute([$p['username']]);
    $row = $stmtUserId->fetch();
    if (!$row) {
        echo "  ⚠️  Utente non trovato per preventivo: {$p['username']} (saltato)\n";
        continue;
    }
    $p['user_id'] = $row['id'];
    unset($p['username']);
    $stmtPreventivo->execute($p);
    echo "  ✅ Inserito preventivo: {$p['marca_moto']} {$p['modello_moto']} — {$p['stato']}\n";
}

echo "\n========================================\n";
echo "  CREDENZIALI PER I TEST\n";
echo "========================================\n";
echo "  Utente normale:\n";
echo "    Username : marco.rossi92\n";
echo "    Password : Test@User1\n";
echo "    Email    : marco.rossi92@email.it\n\n";
echo "  Professionista:\n";
echo "    Username : trasporti.esposito\n";
echo "    Password : Pro@Moto2025\n";
echo "    Email    : info@trasportiesposito.it\n";
echo "========================================\n\n";
