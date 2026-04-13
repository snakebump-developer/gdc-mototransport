<?php
require_once __DIR__ . '/../../../src/auth.php';
require_once __DIR__ . '/../../../src/orders.php';
require_once __DIR__ . '/../../../src/users.php';

requireAdmin();

$user          = getCurrentUser();
$isAdmin       = true;
$section       = 'preventivi';
$pageTitle     = 'Dettaglio Preventivo - Admin';
$noFontAwesome = true;
$extraCss      = ['css/modules/dashboard.css'];

$preventivoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($preventivoId <= 0) {
    header('Location: /admin/preventivi');
    exit;
}

$preventivo = getPreventivoById($preventivoId);
if (!$preventivo) {
    header('Location: /admin/preventivi?error=Preventivo+non+trovato');
    exit;
}

// Cliente collegato (se registrato)
$cliente = null;
if (!empty($preventivo['user_id'])) {
    $cliente = getUserById((int)$preventivo['user_id']);
}

// Recupera anche i dati pagamento
$stmtPg = $pdo->prepare("SELECT * FROM pagamenti WHERE preventivo_id = ? LIMIT 1");
$stmtPg->execute([$preventivoId]);
$pagamentoRow = $stmtPg->fetch() ?: null;

// Preventivo pagato? (per mostrare opzione rimborso)
$isPagato = !empty($pagamentoRow['stripe_payment_intent_id'])
    && in_array($pagamentoRow['stato'] ?? '', ['paid', 'pagato'], true);

// Gestione aggiornamento stato via POST
$success = '';
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_preventivo_stato') {
    try {
        updatePreventivoStato($preventivoId, $_POST['stato']);
        $success  = 'Stato preventivo aggiornato!';
        $preventivo['stato'] = $_POST['stato'];
        // Aggiorna flag pagato in base al nuovo stato
        if ($_POST['stato'] === 'annullato') {
            $isPagato = false;
        }
    } catch (Exception $e) {
        $error = 'Errore: ' . $e->getMessage();
    }
}

// Helper testo
function val(mixed $v, string $empty = '—'): string
{
    $s = trim((string)$v);
    if ($s === '') {
        return '<span class="ud-field__value--empty">' . htmlspecialchars($empty) . '</span>';
    }
    return htmlspecialchars($s);
}

$statiColori = [
    'bozza'         => ['bg' => '#f3f4f6', 'fg' => '#374151'],
    'inviato'       => ['bg' => '#dbeafe', 'fg' => '#1e40af'],
    'confermato'    => ['bg' => '#fef3c7', 'fg' => '#92400e'],
    'in_lavorazione' => ['bg' => '#ede9fe', 'fg' => '#5b21b6'],
    'completato'    => ['bg' => '#d1fae5', 'fg' => '#065f46'],
    'annullato'     => ['bg' => '#fee2e2', 'fg' => '#991b1b'],
];
$statoAttivo = $preventivo['stato'] ?? 'bozza';
$statoColore = $statiColori[$statoAttivo] ?? $statiColori['bozza'];
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include __DIR__ . '/../../includes/head.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../../includes/navbar-dashboard.php'; ?>

    <div class="dashboard-container">
        <?php include __DIR__ . '/../../includes/sidebar-admin.php'; ?>

        <main class="dashboard-main">

            <!-- Breadcrumb -->
            <p style="font-size:.85rem;color:#6b7280;margin-bottom:1.25rem;">
                <a href="/admin/preventivi"
                    style="color:inherit;text-decoration:none;">&larr; Torna ai Preventivi</a>
            </p>

            <?php include __DIR__ . '/../../includes/alerts.php'; ?>

            <!-- Header preventivo -->
            <div class="ud-header">
                <div class="ud-avatar"
                    style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);font-size:1.5rem;">
                    📋
                </div>
                <div class="ud-header__info">
                    <h1 class="ud-header__name">
                        Preventivo #<?= $preventivo['id'] ?>
                        &mdash;
                        <?= htmlspecialchars(
                            trim(($preventivo['marca_moto'] ?? '') . ' ' . ($preventivo['modello_moto'] ?? ''))
                                ?: 'Moto non specificata'
                        ) ?>
                    </h1>
                    <div class="ud-header__sub">
                        <span>Creato il <?= date('d/m/Y H:i', strtotime($preventivo['creato_il'])) ?></span>
                        <?php if ($preventivo['aggiornato_il'] !== $preventivo['creato_il']): ?>
                            <span style="color:#d1d5db;">|</span>
                            <span>Aggiornato il <?= date('d/m/Y H:i', strtotime($preventivo['aggiornato_il'])) ?></span>
                        <?php endif; ?>
                        <span style="color:#d1d5db;">|</span>
                        <span class="ud-stato ud-stato--<?= htmlspecialchars($statoAttivo) ?>">
                            <?= ucfirst(str_replace('_', ' ', $statoAttivo)) ?>
                        </span>
                    </div>
                </div>
                <!-- Cambio stato rapido -->
                <div class="ud-header__actions">
                    <form method="POST" id="formStatoPreventivo" style="display:flex;align-items:center;gap:.5rem;">
                        <input type="hidden" name="action" value="update_preventivo_stato">
                        <select name="stato" class="status-select"
                            onchange="handleStatoChange(this, <?= $isPagato ? 'true' : 'false' ?>, <?= $preventivoId ?>)">
                            <?php foreach (array_keys($statiColori) as $s): ?>
                                <option value="<?= $s ?>" <?= $statoAttivo === $s ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Griglia sezioni -->
            <div class="ud-grid">

                <!-- Dati cliente -->
                <div class="ud-section">
                    <h2 class="ud-section__title">👤 Dati Cliente</h2>
                    <div class="ud-fields">
                        <div class="ud-field" style="grid-column:1/-1;">
                            <span class="ud-field__label">Nome cliente</span>
                            <span class="ud-field__value"><?= val($preventivo['nome_cliente']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Email</span>
                            <span class="ud-field__value">
                                <?php if (!empty($preventivo['email_cliente'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($preventivo['email_cliente']) ?>"
                                        style="color:inherit;"><?= htmlspecialchars($preventivo['email_cliente']) ?></a>
                                <?php else: ?>
                                    <span class="ud-field__value--empty">—</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Telefono</span>
                            <span class="ud-field__value"><?= val($preventivo['telefono_cliente']) ?></span>
                        </div>
                        <?php if ($cliente): ?>
                            <div class="ud-field" style="grid-column:1/-1;">
                                <span class="ud-field__label">Account collegato</span>
                                <span class="ud-field__value">
                                    <a href="/admin/utente/<?= $cliente['id'] ?>"
                                        style="color:var(--primary-color,#e85252);font-weight:600;">
                                        @<?= htmlspecialchars($cliente['username']) ?>
                                        <span class="ov-role ov-role--<?= $cliente['ruolo'] ?>"
                                            style="margin-left:.4rem;">
                                            <?= $cliente['ruolo'] === 'professional' ? 'Professionista' : ucfirst($cliente['ruolo']) ?>
                                        </span>
                                    </a>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="ud-field" style="grid-column:1/-1;">
                                <span class="ud-field__label">Account collegato</span>
                                <span class="ud-field__value--empty">Cliente non registrato</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dati moto -->
                <div class="ud-section">
                    <h2 class="ud-section__title">🏍️ Moto</h2>
                    <div class="ud-fields">
                        <div class="ud-field">
                            <span class="ud-field__label">Marca</span>
                            <span class="ud-field__value"><?= val($preventivo['marca_moto']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Modello</span>
                            <span class="ud-field__value"><?= val($preventivo['modello_moto']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Anno</span>
                            <span class="ud-field__value"><?= val($preventivo['anno_moto']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Cilindrata</span>
                            <span class="ud-field__value">
                                <?= !empty($preventivo['cilindrata'])
                                    ? htmlspecialchars($preventivo['cilindrata']) . ' cc'
                                    : '<span class="ud-field__value--empty">—</span>' ?>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Targa</span>
                            <span class="ud-field__value"><?= val($preventivo['targa']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Percorso -->
                <div class="ud-section">
                    <h2 class="ud-section__title">📍 Percorso di Trasporto</h2>
                    <div class="ud-fields ud-fields--single">
                        <div class="ud-field">
                            <span class="ud-field__label">Indirizzo di Ritiro</span>
                            <span class="ud-field__value"><?= val($preventivo['indirizzo_ritiro']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Indirizzo di Consegna</span>
                            <span class="ud-field__value"><?= val($preventivo['indirizzo_consegna']) ?></span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Distanza stimata</span>
                            <span class="ud-field__value">
                                <?= !empty($preventivo['distanza_km'])
                                    ? number_format((float)$preventivo['distanza_km'], 1, ',', '.') . ' km'
                                    : '<span class="ud-field__value--empty">—</span>' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Riepilogo economico -->
                <div class="ud-section">
                    <h2 class="ud-section__title">💶 Riepilogo Economico</h2>
                    <div class="ud-fields">
                        <div class="ud-field">
                            <span class="ud-field__label">Prezzo base</span>
                            <span class="ud-field__value">
                                &euro;<?= number_format((float)($preventivo['prezzo_base'] ?? 0), 2, ',', '.') ?>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Sconto applicato</span>
                            <span class="ud-field__value">
                                <?php $sconto = (float)($preventivo['sconto_applicato'] ?? 0); ?>
                                <?php if ($sconto > 0): ?>
                                    <span style="color:#059669;font-weight:600;">
                                        &minus;&euro;<?= number_format($sconto, 2, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="ud-field__value--empty">Nessuno</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="ud-field" style="grid-column:1/-1;border-top:2px solid #f3f4f6;padding-top:.75rem;margin-top:.25rem;">
                            <span class="ud-field__label">Prezzo Finale</span>
                            <span class="ud-field__value"
                                style="font-size:1.5rem;font-weight:700;color:#111827;">
                                &euro;<?= number_format((float)($preventivo['prezzo_finale'] ?? 0), 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stato & Note -->
                <div class="ud-section ud-section--full">
                    <h2 class="ud-section__title">📝 Stato & Note</h2>
                    <div class="ud-fields">
                        <div class="ud-field">
                            <span class="ud-field__label">Stato attuale</span>
                            <span class="ud-field__value">
                                <span class="ud-stato ud-stato--<?= htmlspecialchars($statoAttivo) ?>"
                                    style="font-size:.875rem;padding:.3rem .75rem;">
                                    <?= ucfirst(str_replace('_', ' ', $statoAttivo)) ?>
                                </span>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Data creazione</span>
                            <span class="ud-field__value">
                                <?= date('d/m/Y \a\l\l\e H:i', strtotime($preventivo['creato_il'])) ?>
                            </span>
                        </div>
                        <div class="ud-field">
                            <span class="ud-field__label">Ultimo aggiornamento</span>
                            <span class="ud-field__value">
                                <?= date('d/m/Y \a\l\l\e H:i', strtotime($preventivo['aggiornato_il'])) ?>
                            </span>
                        </div>
                        <div class="ud-field" style="grid-column:1/-1;">
                            <span class="ud-field__label">Note</span>
                            <span class="ud-field__value">
                                <?= !empty(trim($preventivo['note'] ?? ''))
                                    ? nl2br(htmlspecialchars($preventivo['note']))
                                    : '<span class="ud-field__value--empty">Nessuna nota inserita</span>' ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div><!-- /ud-grid -->

        </main>
    </div>

    <script src="/js/modules/nav.js"></script>
    <?php include __DIR__ . '/../../includes/whatsapp-button.php'; ?>

    <!-- ── Modale rimborso ───────────────────────────────────────────── -->
    <div id="refundModal" role="dialog" aria-modal="true" aria-labelledby="refundModalTitle"
        style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(0,0,0,.55);
               align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;padding:2rem;max-width:440px;width:90%;
                    box-shadow:0 8px 40px rgba(0,0,0,.18);">
            <h2 id="refundModalTitle" style="margin:0 0 .5rem;font-size:1.15rem;color:#111827;">
                ⚠️ Annullamento preventivo pagato
            </h2>
            <p style="color:#4b5563;margin:.5rem 0 1.5rem;font-size:.9rem;line-height:1.5;">
                Questo preventivo risulta <strong>già pagato</strong>.<br>
                Vuoi avviare il <strong>rimborso automatico su Stripe</strong> contestualmente all'annullamento,
                oppure annullare senza rimborsare?
            </p>
            <div id="refundModalMsg" style="display:none;padding:.6rem .9rem;border-radius:6px;
                 margin-bottom:1rem;font-size:.875rem;font-weight:500;"></div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.6rem;margin-top:.25rem;">
                <button type="button" onclick="closeRefundModal()"
                    style="padding:.6rem .9rem;border:1px solid #d1d5db;border-radius:7px;
                           background:#fff;color:#374151;cursor:pointer;font-size:.875rem;
                           white-space:nowrap;text-align:center;">
                    Annulla operazione
                </button>
                <button type="button" onclick="doCancel(false)"
                    style="padding:.6rem .9rem;border:0;border-radius:7px;
                           background:#f3f4f6;color:#374151;cursor:pointer;font-size:.875rem;
                           white-space:nowrap;text-align:center;">
                    Annulla senza rimborso
                </button>
                <button type="button" onclick="doCancel(true)" id="btnConfirmRefund"
                    style="padding:.6rem .9rem;border:0;border-radius:7px;
                           background:#e85252;color:#fff;cursor:pointer;font-size:.875rem;font-weight:600;
                           white-space:nowrap;text-align:center;">
                    Annulla &amp; Rimborsa
                </button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var _preventivoId = 0;
            var _selectEl = null;
            var _prevVal = '';

            window.handleStatoChange = function(sel, isPagato, preventivoId) {
                if (sel.value === 'annullato' && isPagato) {
                    _selectEl = sel;
                    _prevVal = '<?= htmlspecialchars($statoAttivo) ?>';
                    _preventivoId = preventivoId;
                    openRefundModal();
                } else {
                    document.getElementById('formStatoPreventivo').submit();
                }
            };

            function openRefundModal() {
                var m = document.getElementById('refundModal');
                m.style.display = 'flex';
                document.getElementById('refundModalMsg').style.display = 'none';
            }

            window.closeRefundModal = function() {
                document.getElementById('refundModal').style.display = 'none';
                if (_selectEl) {
                    _selectEl.value = _prevVal;
                }
            };

            window.doCancel = function(withRefund) {
                var btn = document.getElementById('btnConfirmRefund');
                btn.disabled = true;
                btn.textContent = 'Elaborazione…';

                fetch('/api/refund-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            preventivo_id: _preventivoId,
                            motivo: withRefund ? 'requested_by_customer' : null,
                            skip_refund: !withRefund
                        })
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            showMsg('success', '✔ ' + data.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            showMsg('error', '✖ ' + (data.error || 'Errore sconosciuto'));
                            btn.disabled = false;
                            btn.textContent = 'Annulla & Rimborsa';
                        }
                    })
                    .catch(function() {
                        showMsg('error', '✖ Errore di rete. Riprova.');
                        btn.disabled = false;
                        btn.textContent = 'Annulla & Rimborsa';
                    });
            };

            function showMsg(type, text) {
                var el = document.getElementById('refundModalMsg');
                el.style.display = 'block';
                el.style.background = type === 'success' ? '#d1fae5' : '#fee2e2';
                el.style.color = type === 'success' ? '#065f46' : '#991b1b';
                el.textContent = text;
            }

            // Chiudi cliccando fuori dalla card
            document.getElementById('refundModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    window.closeRefundModal();
                }
            });
        }());
    </script>
</body>

</html>