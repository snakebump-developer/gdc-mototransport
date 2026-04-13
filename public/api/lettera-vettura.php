<?php

/**
 * API: Genera e scarica la Lettera di Vettura (Documento di Trasporto)
 * in formato PDF per un preventivo pagato.
 *
 * Endpoint: GET /api/lettera-vettura?id={preventivo_id}
 *
 * Accessibile solo agli amministratori.
 * Documento conforme a D.Lgs. 286/2005 e Art. 1678 e ss. Codice Civile.
 */

require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../src/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Solo admin
requireAdmin();

$preventivoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($preventivoId <= 0) {
    http_response_code(400);
    exit('Parametro ID non valido.');
}

// Carica preventivo con dati utente
$stmt = $pdo->prepare("
    SELECT p.*, u.nome AS u_nome, u.cognome AS u_cognome, u.email AS u_email,
           u.telefono AS u_telefono, u.indirizzo AS u_indirizzo,
           u.citta AS u_citta, u.cap AS u_cap,
           u.ragione_sociale, u.partita_iva, u.codice_fiscale_azienda,
           u.ruolo AS u_ruolo
    FROM preventivi p
    LEFT JOIN utenti u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$preventivoId]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    exit('Preventivo non trovato.');
}

// Solo preventivi pagati
if ($p['pagamento_stato'] !== 'pagato') {
    http_response_code(403);
    exit('Il documento di trasporto è disponibile solo per i preventivi pagati.');
}

// Carica configurazione
$cfg          = require __DIR__ . '/../../src/config.php';
$vettore      = $cfg['vettore'];
$conducente   = $cfg['conducente'];
$mezzo        = $cfg['mezzo'];
$assicurazione = $cfg['assicurazione'];
$company      = $cfg['company'];

// ── Calcola numero documento progressivo ─────────────────────────────────────
// Formato: LV-{ANNO}-{ID a 5 cifre}
$anno       = date('Y', strtotime($p['creato_il']));
$numeroDoc  = 'LV-' . $anno . '-' . str_pad($p['id'], 5, '0', STR_PAD_LEFT);

// ── Dati mittente/destinatario ────────────────────────────────────────────────
$nomeMittente = trim($p['nome_cliente'] ?? '');
if (empty($nomeMittente) && !empty($p['u_nome'])) {
    $nomeMittente = trim(($p['u_nome'] ?? '') . ' ' . ($p['u_cognome'] ?? ''));
}
if (empty($nomeMittente)) {
    $nomeMittente = 'Cliente non registrato';
}

$emailMittente    = $p['email_cliente'] ?? $p['u_email'] ?? '';
$telefonoMittente = $p['telefono_cliente'] ?? $p['u_telefono'] ?? '';

// Il mittente coincide con il destinatario (il cliente paga per trasporto moto)
$isProfessional = ($p['u_ruolo'] ?? '') === 'professional';
$ragioneSociale = $isProfessional && !empty($p['ragione_sociale']) ? $p['ragione_sociale'] : '';
$piva           = $isProfessional && !empty($p['partita_iva'])     ? $p['partita_iva']     : '';

// ── Helper testuale ───────────────────────────────────────────────────────────
function h(mixed $v, string $fallback = '—'): string
{
    $s = trim((string)$v);
    return $s !== '' ? htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8') : htmlspecialchars($fallback, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// ── Data ritiro formattata ────────────────────────────────────────────────────
$dataRitiro = !empty($p['data_ritiro'])
    ? date('d/m/Y', strtotime($p['data_ritiro']))
    : '—';

// ── Descrizione moto ─────────────────────────────────────────────────────────
$descrizioneMoto = trim(($p['marca_moto'] ?? '') . ' ' . ($p['modello_moto'] ?? ''));
if (!empty($p['anno_moto'])) {
    $descrizioneMoto .= ' (' . $p['anno_moto'] . ')';
}
if (empty($descrizioneMoto)) {
    $descrizioneMoto = 'Motociclo';
}

// ── Costruzione HTML del documento ───────────────────────────────────────────
ob_start();
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Lettera di Vettura <?= h($numeroDoc) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            line-height: 1.35;
        }

        /* ── Intestazione ── */
        .header {
            border-bottom: 3px solid #1e3a5f;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e3a5f;
            letter-spacing: 0.5px;
        }

        .company-details {
            font-size: 7.5pt;
            color: #555;
            margin-top: 2px;
        }

        .doc-title-block {
            text-align: right;
        }

        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .doc-subtitle {
            font-size: 7.5pt;
            color: #777;
            margin-top: 1px;
        }

        .doc-number {
            font-size: 10pt;
            font-weight: bold;
            color: #c0392b;
            margin-top: 3px;
        }

        .doc-date {
            font-size: 8pt;
            color: #555;
            margin-top: 2px;
        }

        /* ── Avviso legale ── */
        .legal-notice {
            background: #fef9e7;
            border: 1px solid #f0c040;
            border-radius: 3px;
            padding: 5px 8px;
            font-size: 7.5pt;
            color: #7d6608;
            margin-bottom: 10px;
        }

        /* ── Sezioni ── */
        .section-title {
            background: #1e3a5f;
            color: #fff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 7px;
            margin-bottom: 0;
        }

        .section-box {
            border: 1px solid #c8d0da;
            margin-bottom: 8px;
        }

        .section-body {
            padding: 7px 8px;
        }

        /* ── Layout 2 colonne ── */
        .two-col {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .two-col>tbody>tr>td {
            vertical-align: top;
            width: 50%;
        }

        .two-col>tbody>tr>td:first-child {
            padding-right: 5px;
        }

        .two-col>tbody>tr>td:last-child {
            padding-left: 5px;
        }

        /* ── Campo dati ── */
        .field-row {
            margin-bottom: 4px;
            display: flex;
            align-items: baseline;
        }

        .field-label {
            font-size: 7pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            min-width: 95px;
            flex-shrink: 0;
        }

        .field-value {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1a1a1a;
        }

        /* ── Tabella merce ── */
        .goods-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 8px;
        }

        .goods-table th {
            background: #1e3a5f;
            color: #fff;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.3px;
        }

        .goods-table td {
            border: 1px solid #dce3ea;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .goods-table tr:nth-child(even) td {
            background: #f7f9fb;
        }

        /* ── Sezione firme ── */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .signature-table td {
            width: 33.33%;
            border: 1px solid #c8d0da;
            padding: 8px;
            vertical-align: top;
        }

        .signature-label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a5f;
            letter-spacing: 0.3px;
            margin-bottom: 4px;
        }

        .signature-name {
            font-size: 8pt;
            color: #444;
            margin-bottom: 20px;
        }

        .signature-line {
            border-top: 1px solid #888;
            margin-top: 5px;
            padding-top: 2px;
            font-size: 7pt;
            color: #aaa;
        }

        /* ── Condizioni ── */
        .conditions-box {
            border: 1px solid #c8d0da;
            padding: 6px 8px;
            font-size: 7pt;
            color: #555;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .conditions-title {
            font-weight: bold;
            font-size: 7.5pt;
            color: #1e3a5f;
            margin-bottom: 3px;
        }

        /* ── Footer ── */
        .doc-footer {
            border-top: 2px solid #1e3a5f;
            padding-top: 6px;
            margin-top: 8px;
            text-align: center;
            font-size: 7pt;
            color: #888;
        }

        /* ── Badge pagato ── */
        .badge-paid {
            display: inline-block;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            padding: 2px 7px;
            letter-spacing: 0.5px;
        }
    </style>
</head>

<body>

    <!-- ══ INTESTAZIONE ══ -->
    <div class="header">
        <div class="header-top">
            <div>
                <div class="company-name"><?= h($vettore['ragione_sociale']) ?></div>
                <div class="company-details">
                    <?= h($vettore['indirizzo']) ?>, <?= h($vettore['cap']) ?> <?= h($vettore['citta']) ?> (<?= h($vettore['provincia']) ?>)<br>
                    P.IVA: <?= h($vettore['piva']) ?> &nbsp;|&nbsp; Tel: <?= h($vettore['telefono']) ?> &nbsp;|&nbsp; <?= h($vettore['email']) ?><br>
                    Albo Autotrasportatori (REN): <strong><?= h($vettore['ren']) ?></strong>
                </div>
            </div>
            <div class="doc-title-block">
                <div class="doc-title">Lettera di Vettura</div>
                <div class="doc-subtitle">Documento di Trasporto — Uso Nazionale</div>
                <div class="doc-number">N° <?= h($numeroDoc) ?></div>
                <div class="doc-date">
                    Emesso il: <strong><?= date('d/m/Y') ?></strong>
                    &nbsp;|&nbsp; Data ritiro: <strong><?= h($dataRitiro) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ AVVISO LEGALE ══ -->
    <div class="legal-notice">
        <strong>Documento valido ai sensi del D.Lgs. 286/2005 (Contratto di trasporto) e Art. 1678 e ss. del Codice Civile.</strong>
        Esibire obbligatoriamente alle autorità competenti durante il trasporto. Conservare per 5 anni.
    </div>

    <!-- ══ MITTENTE / DESTINATARIO ══ -->
    <table class="two-col">
        <tbody>
            <tr>
                <td>
                    <div class="section-box">
                        <div class="section-title">Mittente (Committente)</div>
                        <div class="section-body">
                            <div class="field-row">
                                <span class="field-label">Nominativo:</span>
                                <span class="field-value">
                                    <?php if ($isProfessional && !empty($ragioneSociale)): ?>
                                        <?= h($ragioneSociale) ?><br>
                                        <small style="font-size:7.5pt;font-weight:normal;">Referente: <?= h($nomeMittente) ?></small>
                                    <?php else: ?>
                                        <?= h($nomeMittente) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if (!empty($piva)): ?>
                                <div class="field-row">
                                    <span class="field-label">P.IVA:</span>
                                    <span class="field-value"><?= h($piva) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($p['codice_fiscale_cliente'])): ?>
                                <div class="field-row">
                                    <span class="field-label">Cod. Fiscale:</span>
                                    <span class="field-value"><?= h($p['codice_fiscale_cliente']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="field-row">
                                <span class="field-label">Email:</span>
                                <span class="field-value"><?= h($emailMittente) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Telefono:</span>
                                <span class="field-value"><?= h($telefonoMittente) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Luogo ritiro:</span>
                                <span class="field-value"><?= h($p['indirizzo_ritiro']) ?></span>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="section-box">
                        <div class="section-title">Destinatario / Luogo consegna</div>
                        <div class="section-body">
                            <div class="field-row">
                                <span class="field-label">Nominativo:</span>
                                <span class="field-value">
                                    <?php if ($isProfessional && !empty($ragioneSociale)): ?>
                                        <?= h($ragioneSociale) ?>
                                    <?php else: ?>
                                        <?= h($nomeMittente) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Luogo consegna:</span>
                                <span class="field-value"><?= h($p['indirizzo_consegna']) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Distanza:</span>
                                <span class="field-value">
                                    <?= $p['distanza_km'] ? number_format((float)$p['distanza_km'], 0, ',', '.') . ' km' : '—' ?>
                                </span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Tipo consegna:</span>
                                <span class="field-value"><?= h($p['tipo_consegna'] ?? 'Standard') ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Stato pagamento:</span>
                                <span class="badge-paid">&#10003; PAGATO</span>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ══ DESCRIZIONE MERCE ══ -->
    <div class="section-box">
        <div class="section-title">Descrizione della Merce da Trasportare</div>
        <div class="section-body" style="padding:0;">
            <table class="goods-table">
                <thead>
                    <tr>
                        <th style="width:5%">N°</th>
                        <th style="width:22%">Descrizione</th>
                        <th style="width:15%">Marca / Modello</th>
                        <th style="width:7%">Anno</th>
                        <th style="width:12%">Targa</th>
                        <th style="width:13%">Cilindrata</th>
                        <th style="width:13%">Accessori</th>
                        <th style="width:13%">Valore dichiarato</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align:center">1</td>
                        <td><?= h($descrizioneMoto) ?></td>
                        <td><?= h(trim(($p['marca_moto'] ?? '') . ' ' . ($p['modello_moto'] ?? ''))) ?></td>
                        <td><?= h($p['anno_moto'] ?? '—') ?></td>
                        <td><strong><?= h($p['targa'] ?? '—') ?></strong></td>
                        <td><?= $p['cilindrata'] ? h($p['cilindrata']) . ' cc' : '—' ?></td>
                        <td>
                            <?php
                            $borse = (float)($p['borse_laterali'] ?? 0);
                            echo $borse > 0 ? 'Borse lat. +€' . number_format($borse, 0) : 'Nessuno';
                            ?>
                        </td>
                        <td>€ <?= number_format((float)($p['prezzo_finale'] ?? 0), 2, ',', '.') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ VETTORE / CONDUCENTE / MEZZO ══ -->
    <table class="two-col">
        <tbody>
            <tr>
                <td>
                    <div class="section-box">
                        <div class="section-title">Conducente</div>
                        <div class="section-body">
                            <div class="field-row">
                                <span class="field-label">Nominativo:</span>
                                <span class="field-value"><?= h($conducente['nome'] . ' ' . $conducente['cognome']) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">N° Patente:</span>
                                <span class="field-value"><?= h($conducente['patente']) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Categoria:</span>
                                <span class="field-value"><?= h($conducente['patente_cat']) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Telefono:</span>
                                <span class="field-value"><?= h($conducente['telefono']) ?></span>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="section-box">
                        <div class="section-title">Mezzo di Trasporto</div>
                        <div class="section-body">
                            <div class="field-row">
                                <span class="field-label">Tipo:</span>
                                <span class="field-value"><?= h($mezzo['tipo']) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Marca / Modello:</span>
                                <span class="field-value"><?= h($mezzo['marca'] . ' ' . $mezzo['modello']) ?></span>
                            </div>
                            <div class="field-row">
                                <span class="field-label">Targa:</span>
                                <span class="field-value"><?= h($mezzo['targa']) ?></span>
                            </div>
                            <?php if (!empty($mezzo['rimorchio_targa'])): ?>
                                <div class="field-row">
                                    <span class="field-label">Rimorchio:</span>
                                    <span class="field-value"><?= h($mezzo['rimorchio_targa']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ══ ASSICURAZIONE ══ -->
    <div class="section-box">
        <div class="section-title">Assicurazione RC Merci del Vettore</div>
        <div class="section-body">
            <table style="width:100%;border-collapse:collapse;">
                <tbody>
                    <tr>
                        <td style="width:25%">
                            <div class="field-row">
                                <span class="field-label">Compagnia:</span>
                                <span class="field-value"><?= h($assicurazione['compagnia']) ?></span>
                            </div>
                        </td>
                        <td style="width:25%">
                            <div class="field-row">
                                <span class="field-label">N° Polizza:</span>
                                <span class="field-value"><?= h($assicurazione['polizza']) ?></span>
                            </div>
                        </td>
                        <td style="width:25%">
                            <div class="field-row">
                                <span class="field-label">Scadenza:</span>
                                <span class="field-value"><?= h($assicurazione['scadenza']) ?></span>
                            </div>
                        </td>
                        <td style="width:25%">
                            <div class="field-row">
                                <span class="field-label">Massimale:</span>
                                <span class="field-value"><?= h($assicurazione['massimale']) ?></span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ NOTE CLIENTI ══ -->
    <?php if (!empty($p['note'])): ?>
        <div class="section-box">
            <div class="section-title">Note / Istruzioni speciali</div>
            <div class="section-body" style="font-size:8pt;">
                <?= h($p['note']) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══ CONDIZIONI ══ -->
    <div class="conditions-box">
        <div class="conditions-title">Condizioni e Dichiarazioni</div>
        Il vettore dichiara di ricevere il mezzo nelle condizioni visibili al momento del ritiro e di consegnarlo nelle medesime condizioni, salvo accertati danni nel percorso.
        Il committente dichiara che le informazioni fornite sul bene trasportato sono veritiere e complete.
        Responsabilità del vettore limitata ai sensi degli Art. 1693 e 1694 del Codice Civile e del D.Lgs. 286/2005.
        Foro competente: Tribunale di <?= h($vettore['citta']) ?>.
    </div>

    <!-- ══ RIQUADRI FIRMA ══ -->
    <table class="signature-table">
        <tbody>
            <tr>
                <td>
                    <div class="signature-label">Firma Mittente (Committente)</div>
                    <div class="signature-name"><?= h($nomeMittente) ?></div>
                    <div class="signature-line">Firma: ____________________________</div>
                    <div style="font-size:7pt;color:#aaa;margin-top:2px;">Data: ______ / ______ / __________</div>
                </td>
                <td>
                    <div class="signature-label">Firma Vettore</div>
                    <div class="signature-name"><?= h($vettore['ragione_sociale']) ?></div>
                    <div class="signature-line">Firma: ____________________________</div>
                    <div style="font-size:7pt;color:#aaa;margin-top:2px;">Data: ______ / ______ / __________</div>
                </td>
                <td>
                    <div class="signature-label">Firma Destinatario (Consegna)</div>
                    <div class="signature-name"><?= h($nomeMittente) ?></div>
                    <div class="signature-line">Firma: ____________________________</div>
                    <div style="font-size:7pt;color:#aaa;margin-top:2px;">Data: ______ / ______ / __________</div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ══ FOOTER ══ -->
    <div class="doc-footer">
        Documento generato il <?= date('d/m/Y \a\l\l\e H:i') ?> da <?= h($vettore['ragione_sociale']) ?>
        &nbsp;|&nbsp; Ref. Preventivo #<?= h($p['id']) ?>
        &nbsp;|&nbsp; <?= h($vettore['email']) ?>
        &nbsp;|&nbsp; <?= h($vettore['telefono']) ?>
    </div>

</body>

</html>
<?php
$html = ob_get_clean();

// ── Genera PDF con mPDF ───────────────────────────────────────────────────────
try {
    $tmpDir = sys_get_temp_dir() . '/mpdf_' . uniqid();
    mkdir($tmpDir, 0700, true);

    $mpdf = new \Mpdf\Mpdf([
        'mode'              => 'utf-8',
        'format'            => 'A4',
        'orientation'       => 'L',   // Landscape — più leggibile per questa struttura
        'margin_top'        => 10,
        'margin_bottom'     => 10,
        'margin_left'       => 12,
        'margin_right'      => 12,
        'tempDir'           => $tmpDir,
        'default_font'      => 'dejavusans',
    ]);

    $mpdf->SetTitle('Lettera di Vettura ' . $numeroDoc);
    $mpdf->SetAuthor($vettore['ragione_sociale']);
    $mpdf->SetCreator($vettore['ragione_sociale']);

    $mpdf->WriteHTML($html);

    $filename = 'lettera-vettura-' . $preventivoId . '.pdf';

    // Streaming diretto al browser
    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
} catch (\Mpdf\MpdfException $e) {
    http_response_code(500);
    exit('Errore generazione PDF: ' . $e->getMessage());
} finally {
    // Pulizia directory temporanea
    if (is_dir($tmpDir)) {
        array_map('unlink', glob($tmpDir . '/*') ?: []);
        @rmdir($tmpDir);
    }
}
