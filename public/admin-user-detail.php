<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/users.php';

requireAdmin();

$user       = getCurrentUser();
$isAdmin    = true;
$pageTitle  = 'Dettaglio Utente - Admin';
$noFontAwesome = true;
$extraCss   = ['css/modules/dashboard.css'];
$section    = 'users'; // usato dalla sidebar per evidenziare la voce

// Parametri
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$back   = in_array($_GET['back'] ?? '', ['users', 'professionals']) ? $_GET['back'] : 'users';

if ($userId <= 0) {
    header('Location: admin.php?section=' . $back);
    exit;
}

// Carica utente completo
$soggetto = getUserById($userId);
if (!$soggetto) {
    header('Location: admin.php?section=' . $back . '&error=Utente+non+trovato');
    exit;
}

// Aggiorna la sezione sidebar in base al ruolo
if ($soggetto['ruolo'] === 'professional') {
    $section = 'professionals';
}

// Preventivi collegati
$preventivi_utente = getUserPreventivi($userId);

// Helper per mostrare un valore o un placeholder
function val(mixed $v, string $empty = '—'): string
{
    $s = trim((string)$v);
    if ($s === '' || $s === null) {
        return '<span class="ud-field__value--empty">' . htmlspecialchars($empty) . '</span>';
    }
    return htmlspecialchars($s);
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <?php include 'includes/navbar-dashboard.php'; ?>

    <div class="dashboard-container">
        <?php include 'includes/sidebar-admin.php'; ?>

        <main class="dashboard-main">

            <!-- Breadcrumb -->
            <p style="font-size:.85rem;color:#6b7280;margin-bottom:1.25rem;">
                <a href="admin.php?section=<?= $back ?>"
                    style="color:inherit;text-decoration:none;">&larr; Torna a
                    <?= $back === 'professionals' ? 'Professionisti' : 'Clienti' ?>
                </a>
            </p>

            <!-- Header utente -->
            <div class="ud-header">
                <div class="ud-avatar <?= $soggetto['ruolo'] === 'professional' ? 'ud-avatar--professional' : '' ?>">
                    <?= mb_strtoupper(mb_substr($soggetto['nome'] ?: $soggetto['username'], 0, 1)) ?>
                </div>
                <div class="ud-header__info">
                    <h1 class="ud-header__name">
                        <?php
                        if ($soggetto['ruolo'] === 'professional' && !empty($soggetto['ragione_sociale'])) {
                            echo htmlspecialchars($soggetto['ragione_sociale']);
                        } else {
                            echo htmlspecialchars(trim(($soggetto['nome'] ?? '') . ' ' . ($soggetto['cognome'] ?? '')) ?: $soggetto['username']);
                        }
                        ?>
                    </h1>
                    <div class="ud-header__sub">
                        <span>@<?= htmlspecialchars($soggetto['username']) ?></span>
                        <span style="color:#d1d5db;">|</span>
                        <span><?= htmlspecialchars($soggetto['email']) ?></span>
                        <span style="color:#d1d5db;">|</span>
                        <span class="ov-role ov-role--<?= $soggetto['ruolo'] ?>">
                            <?= $soggetto['ruolo'] === 'professional' ? 'Professionista' : ucfirst($soggetto['ruolo']) ?>
                        </span>
                    </div>
                </div>
                <?php if ($soggetto['id'] !== $user['id']): ?>
                    <div class="ud-header__actions">
                        <form method="POST" action="admin.php?section=<?= $back ?>"
                            onsubmit="return confirm('Eliminare definitivamente questo utente?')">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= $soggetto['id'] ?>">
                            <button type="submit" class="btn btn-danger">Elimina utente</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ========================
                 SEZIONI INFO
                 ======================== -->
            <div class="ud-grid">

                <!-- Dati Personali -->
                <div class="ud-section">
                    <h2 class="ud-section__title">👤 Dati Personali</h2>
                    <div class="ud-fields">
                        <div class="ud-field">
                            <span class="ud-field__label">Nome</span>
                            <span class="ud-field__value"><?= val($soggetto['nome']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Cognome</span>
                            <span class="ud-field__value"><?= val($soggetto['cognome']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Email</span>
                            <span class="ud-field__value">
                                <a href="mailto:<?= htmlspecialchars($soggetto['email']) ?>"
                                    style="color:inherit;">
                                    <?= htmlspecialchars($soggetto['email']) ?>
                                </a>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Telefono</span>
                            <span class="ud-field__value"><?= val($soggetto['telefono']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Indirizzo -->
                <div class="ud-section">
                    <h2 class="ud-section__title">📍 Indirizzo</h2>
                    <div class="ud-fields">
                        <div class="ud-field" style="grid-column:1/-1;">
                            <span class="ud-field__label">Via / Indirizzo</span>
                            <span class="ud-field__value"><?= val($soggetto['indirizzo']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Città</span>
                            <span class="ud-field__value"><?= val($soggetto['citta']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">CAP</span>
                            <span class="ud-field__value"><?= val($soggetto['cap']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Paese</span>
                            <span class="ud-field__value"><?= val($soggetto['paese'] ?? 'Italia') ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($soggetto['ruolo'] === 'professional'): ?>

                    <!-- Dati Aziendali -->
                    <div class="ud-section">
                        <h2 class="ud-section__title">🏢 Dati Aziendali</h2>
                        <div class="ud-fields">
                            <div class="ud-field" style="grid-column:1/-1;">
                                <span class="ud-field__label">Ragione Sociale</span>
                                <span class="ud-field__value"><?= val($soggetto['ragione_sociale']) ?></span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">Partita IVA</span>
                                <span class="ud-field__value"><?= val($soggetto['partita_iva']) ?></span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">Codice Fiscale Azienda</span>
                                <span class="ud-field__value"><?= val($soggetto['codice_fiscale_azienda']) ?></span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">PEC</span>
                                <span class="ud-field__value">
                                    <?php if (!empty($soggetto['pec'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($soggetto['pec']) ?>"
                                            style="color:inherit;">
                                            <?= htmlspecialchars($soggetto['pec']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="ud-field__value--empty">—</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">Codice SDI</span>
                                <span class="ud-field__value"><?= val($soggetto['codice_sdi']) ?></span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">Tipo Attività</span>
                                <span class="ud-field__value"><?= val($soggetto['tipo_attivita']) ?></span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">Sconto applicato</span>
                                <span class="ud-field__value">
                                    <?= number_format((float)($soggetto['sconto_percentuale'] ?? 0), 1) ?>%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Indirizzo Fatturazione -->
                    <div class="ud-section">
                        <h2 class="ud-section__title">🧾 Indirizzo Fatturazione</h2>
                        <div class="ud-fields">
                            <div class="ud-field" style="grid-column:1/-1;">
                                <span class="ud-field__label">Via / Indirizzo</span>
                                <span class="ud-field__value"><?= val($soggetto['indirizzo_fatturazione']) ?></span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">Città</span>
                                <span class="ud-field__value"><?= val($soggetto['citta_fatturazione']) ?></span>
                            </div>
                            <div class="ud-field">
                                <span class="ud-field__label">CAP</span>
                                <span class="ud-field__value"><?= val($soggetto['cap_fatturazione']) ?></span>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

                <!-- Dati Account & GDPR -->
                <div class="ud-section">
                    <h2 class="ud-section__title">🔐 Account</h2>
                    <div class="ud-fields">
                        <div class="ud-field">
                            <span class="ud-field__label">Username</span>
                            <span class="ud-field__value"><?= val($soggetto['username']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Ruolo</span>
                            <span class="ud-field__value">
                                <span class="ov-role ov-role--<?= $soggetto['ruolo'] ?>">
                                    <?= $soggetto['ruolo'] === 'professional' ? 'Professionista' : ucfirst($soggetto['ruolo']) ?>
                                </span>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Registrato il</span>
                            <span class="ud-field__value">
                                <?= !empty($soggetto['creato_il']) ? date('d/m/Y H:i', strtotime($soggetto['creato_il'])) : '—' ?>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">GDPR accettato</span>
                            <span class="ud-field__value">
                                <?php if ($soggetto['gdpr_accettato']): ?>
                                    <span style="color:#059669;font-weight:600;">✓ Sì</span>
                                    <?php if (!empty($soggetto['gdpr_accettato_il'])): ?>
                                        <span style="color:#9ca3af;font-size:.8rem;">
                                            (<?= date('d/m/Y', strtotime($soggetto['gdpr_accettato_il'])) ?>)
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#dc2626;font-weight:600;">✗ No</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Marketing</span>
                            <span class="ud-field__value">
                                <?php if ($soggetto['marketing_accettato']): ?>
                                    <span style="color:#059669;font-weight:600;">✓ Accettato</span>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">Non accettato</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Preventivi dell'utente -->
                <div class="ud-section ud-section--full">
                    <h2 class="ud-section__title">📋 Preventivi
                        <span style="font-size:.8rem;color:#9ca3af;font-weight:400;margin-left:.5rem;">
                            (<?= count($preventivi_utente) ?>)
                        </span>
                    </h2>

                    <?php if (empty($preventivi_utente)): ?>
                        <p style="color:#9ca3af;font-style:italic;font-size:.875rem;text-align:center;padding:1.5rem 0;">
                            Nessun preventivo associato a questo utente.
                        </p>
                    <?php else: ?>
                        <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
                            <table class="ud-preventivi-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Moto</th>
                                        <th>Anno</th>
                                        <th>Targa</th>
                                        <th>Ritiro</th>
                                        <th>Consegna</th>
                                        <th>Km</th>
                                        <th>Prezzo base</th>
                                        <th>Sconto</th>
                                        <th>Prezzo finale</th>
                                        <th>Stato</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preventivi_utente as $p): ?>
                                        <tr>
                                            <td>#<?= $p['id'] ?></td>
                                            <td><?= htmlspecialchars(trim(($p['marca_moto'] ?? '') . ' ' . ($p['modello_moto'] ?? ''))) ?></td>
                                            <td><?= $p['anno_moto'] ? htmlspecialchars($p['anno_moto']) : '—' ?></td>
                                            <td><?= !empty($p['targa']) ? htmlspecialchars($p['targa']) : '—' ?></td>
                                            <td class="td-wrap"><?= htmlspecialchars($p['indirizzo_ritiro']) ?></td>
                                            <td class="td-wrap"><?= htmlspecialchars($p['indirizzo_consegna']) ?></td>
                                            <td><?= $p['distanza_km'] ? number_format((float)$p['distanza_km'], 0, ',', '.') . ' km' : '—' ?></td>
                                            <td>&euro;<?= number_format((float)($p['prezzo_base'] ?? 0), 2, ',', '.') ?></td>
                                            <td>
                                                <?= ($p['sconto_applicato'] ?? 0) > 0
                                                    ? '-&euro;' . number_format((float)$p['sconto_applicato'], 2, ',', '.')
                                                    : '—' ?>
                                            </td>
                                            <td style="font-weight:700;">&euro;<?= number_format((float)($p['prezzo_finale'] ?? 0), 2, ',', '.') ?></td>
                                            <td>
                                                <span class="ud-stato ud-stato--<?= htmlspecialchars($p['stato']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $p['stato'])) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($p['creato_il'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div><!-- /overflow wrapper -->
                    <?php endif; ?>
                </div>

            </div><!-- /ud-grid -->

        </main>
    </div>

    <script src="js/modules/nav.js"></script>
</body>

</html>