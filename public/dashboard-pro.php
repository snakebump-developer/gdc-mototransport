<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/motorcycles.php';

requireLogin();
if (!isProfessional() && !isAdmin()) {
    header('Location: /dashboard');
    exit;
}

$user    = getCurrentUser();
$section = $_GET['section'] ?? 'profile';
$success = '';
$error   = '';
$pageTitle     = 'Dashboard Professionista - GDC MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/dashboard.css', 'css/modules/quote-modal.css'];
$config   = require __DIR__ . '/../src/config.php';
$stripePk = htmlspecialchars($config['stripe']['public_key'] ?? '', ENT_QUOTES, 'UTF-8');
$quoteUserData = [
    'nome'     => trim(($user['nome'] ?? '') . ' ' . ($user['cognome'] ?? '')),
    'email'    => $user['email'] ?? '',
    'telefono' => $user['telefono'] ?? '',
    'cf'       => $user['codice_fiscale_azienda'] ?? '',
];

// ---- POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        validateCsrfToken($_POST['csrf_token'] ?? '');

        if ($section === 'profile') {
            // Upload avatar
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['avatar'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Errore durante il caricamento del file.");
                }
                if ($file['size'] > 2 * 1024 * 1024) {
                    throw new Exception("L'immagine non può superare 2 MB.");
                }
                $finfo   = new finfo(FILEINFO_MIME_TYPE);
                $mime    = $finfo->file($file['tmp_name']);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if (!isset($allowed[$mime])) {
                    throw new Exception("Formato non supportato. Usa JPG, PNG, GIF o WebP.");
                }
                $uploadDir = __DIR__ . '/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (!empty($user['avatar'])) {
                    $old = __DIR__ . '/' . $user['avatar'];
                    if (is_file($old)) unlink($old);
                }
                $filename = 'avatar_' . $user['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                    throw new Exception("Impossibile salvare l'immagine.");
                }
                updateUserAvatar($user['id'], 'uploads/avatars/' . $filename);
                $success = "Avatar aggiornato con successo!";
                $user    = getCurrentUser();
            }
            // Rimozione avatar
            if (isset($_POST['remove_avatar'])) {
                if (!empty($user['avatar'])) {
                    $old = __DIR__ . '/' . $user['avatar'];
                    if (is_file($old)) unlink($old);
                }
                removeUserAvatar($user['id']);
                $success = "Avatar rimosso.";
                $user    = getCurrentUser();
            }
            // Aggiornamento dati profilo
            if (!isset($_POST['remove_avatar']) && !(isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE)) {
                updateProfessionalProfile($user['id'], $_POST);
                $success = "Profilo aggiornato!";
                $user    = getCurrentUser();
            }
        } elseif ($section === 'motorcycles' && isset($_POST['add_moto'])) {
            saveMotorcycle($user['id'], $_POST);
            $success = "Moto aggiunta!";
        } elseif ($section === 'motorcycles' && isset($_POST['edit_moto_id'])) {
            updateMotorcycle((int)$_POST['edit_moto_id'], $user['id'], $_POST);
            $success = "Moto aggiornata!";
        } elseif ($section === 'motorcycles' && isset($_POST['delete_moto_id'])) {
            deleteMotorcycle((int)$_POST['delete_moto_id'], $user['id']);
            $success = "Moto rimossa.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$ordini = [];
$bozze  = [];
$moto   = [];
if ($section === 'orders') {
    $ordini = getUserOrders($user['id']);
    $bozze  = getDraftPreventivi($user['id']);
}
if ($section === 'motorcycles') $moto   = getUserMotorcycles($user['id']);

$csrf   = generateCsrfToken();
$sconto = (float)($user['sconto_percentuale'] ?? 10);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <?php include 'includes/navbar-dashboard.php'; ?>

    <div class="dashboard-container">
        <?php include 'includes/sidebar-pro.php'; ?>

        <main class="dashboard-main">
            <?php include 'includes/alerts.php'; ?>

            <!-- Banner sconto professionista -->
            <div class="discount-banner">
                <div class="discount-banner__icon">&#9889;</div>
                <div>
                    <strong class="discount-banner__label">Account Professionista</strong>
                    <span class="discount-banner__text">
                        Hai uno sconto fisso del
                        <strong><?= number_format($sconto, 0) ?>%</strong>
                        su tutti i preventivi per i tuoi clienti.
                    </span>
                </div>
                <?php if (!empty($user['tipo_attivita'])): ?>
                    <span class="badge badge--professional">
                        <?= htmlspecialchars(ucfirst($user['tipo_attivita'])) ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- ==================== PROFILO ==================== -->
            <?php if ($section === 'profile'): ?>
                <div class="dashboard-section">
                    <h1 class="dashboard-section__title">Profilo Professionista</h1>
                    <p class="section-description">I tuoi dati personali e fiscali</p>

                    <!-- Card Avatar -->
                    <div class="avatar-card">
                        <div class="avatar-preview-wrapper">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="/<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Avatar" class="avatar-preview">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?= htmlspecialchars(strtoupper(substr($user['ragione_sociale'] ?? $user['nome'] ?? $user['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="avatar-upload-area">
                            <h3>Foto Profilo</h3>
                            <p class="avatar-hint">JPG, PNG, GIF o WebP &middot; Max 2&nbsp;MB</p>
                            <div class="avatar-actions">
                                <form method="POST" enctype="multipart/form-data" class="avatar-form" id="avatarUploadForm">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <label class="btn btn-secondary btn-sm avatar-upload-btn">
                                        Carica immagine
                                        <input type="file" name="avatar"
                                            accept="image/jpeg,image/png,image/gif,image/webp"
                                            id="avatarFileInput" style="display:none">
                                    </label>
                                </form>
                                <?php if (!empty($user['avatar'])): ?>
                                    <form method="POST" class="avatar-form">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <button type="submit" name="remove_avatar" value="1"
                                            class="btn btn-ghost btn-sm">Rimuovi</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="profile-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                        <div class="profile-form__group-title">Referente aziendale</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-group__label">Username</label>
                                <input class="form-group__input" type="text"
                                    value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-group__label">Email</label>
                                <input class="form-group__input" type="email"
                                    value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-group__label" for="nome">Nome</label>
                                <input class="form-group__input" type="text" id="nome" name="nome"
                                    value="<?= htmlspecialchars($user['nome'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-group__label" for="cognome">Cognome</label>
                                <input class="form-group__input" type="text" id="cognome" name="cognome"
                                    value="<?= htmlspecialchars($user['cognome'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-group__label" for="telefono">Telefono</label>
                                <input class="form-group__input" type="tel" id="telefono" name="telefono"
                                    value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Dati fiscali (sola lettura, modificabili solo da admin) -->
                        <div class="profile-form__group-title">
                            Dati fiscali
                            <span class="badge badge--locked" title="Modificabili solo tramite richiesta all'admin">
                                &#128274; Protetti GDPR
                            </span>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-group__label">Ragione Sociale</label>
                                <input class="form-group__input" type="text"
                                    value="<?= htmlspecialchars($user['ragione_sociale'] ?? '') ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-group__label">Partita IVA</label>
                                <input class="form-group__input" type="text"
                                    value="<?= htmlspecialchars($user['partita_iva'] ?? '') ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-group__label">Codice Fiscale</label>
                                <input class="form-group__input" type="text"
                                    value="<?= htmlspecialchars($user['codice_fiscale_azienda'] ?? '') ?>" disabled>
                            </div>
                        </div>

                        <!-- Fatturazione (modificabili) -->
                        <div class="profile-form__group-title">Indirizzo di fatturazione</div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-group__label" for="pec">PEC</label>
                                <input class="form-group__input" type="email" id="pec" name="pec"
                                    value="<?= htmlspecialchars($user['pec'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-group__label" for="codice_sdi">Codice SDI</label>
                                <input class="form-group__input" type="text" id="codice_sdi" name="codice_sdi"
                                    maxlength="7"
                                    value="<?= htmlspecialchars($user['codice_sdi'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-group__label" for="indirizzo_fatturazione">Indirizzo</label>
                            <input class="form-group__input" type="text" id="indirizzo_fatturazione"
                                name="indirizzo_fatturazione"
                                value="<?= htmlspecialchars($user['indirizzo_fatturazione'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-group__label" for="citta_fatturazione">Citta'</label>
                                <input class="form-group__input" type="text" id="citta_fatturazione"
                                    name="citta_fatturazione"
                                    value="<?= htmlspecialchars($user['citta_fatturazione'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-group__label" for="cap_fatturazione">CAP</label>
                                <input class="form-group__input" type="text" id="cap_fatturazione"
                                    name="cap_fatturazione" maxlength="5"
                                    value="<?= htmlspecialchars($user['cap_fatturazione'] ?? '') ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                    </form>
                </div>

                <!-- ==================== MOTO CLIENTI ==================== -->
            <?php elseif ($section === 'motorcycles'): ?>
                <div class="dashboard-section">
                    <div class="dashboard-section__header">
                        <div>
                            <h1 class="dashboard-section__title">Moto Clienti</h1>
                            <p class="section-description">
                                Gestisci le moto dei tuoi clienti per generare preventivi in modo rapido
                            </p>
                        </div>
                        <button class="btn btn-primary" id="btnAddMoto">+ Aggiungi Moto</button>
                    </div>

                    <div class="moto-form-card" id="addMotoCard" style="display:none;">
                        <h3 class="moto-form-card__title">Aggiungi moto cliente</h3>
                        <form method="POST" class="profile-form" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="add_moto" value="1">
                            <?php include 'includes/moto-fields.php'; ?>
                            <div class="moto-form-card__actions">
                                <button type="submit" class="btn btn-primary">Salva</button>
                                <button type="button" class="btn btn-secondary" id="btnCancelMoto">Annulla</button>
                            </div>
                        </form>
                    </div>

                    <?php if (empty($moto)): ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">&#127949;</div>
                            <h3 class="empty-state__title">Nessuna moto salvata</h3>
                            <p class="empty-state__text">
                                Aggiungi le moto dei tuoi clienti per avere preventivi ancora piu' rapidi.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="moto-grid">
                            <?php foreach ($moto as $m): ?>
                                <div class="moto-card">
                                    <div class="moto-card__header">
                                        <span class="moto-card__icon">&#127949;</span>
                                        <h3 class="moto-card__name">
                                            <?= htmlspecialchars($m['marca']) ?>
                                            <?= htmlspecialchars($m['modello']) ?>
                                        </h3>
                                    </div>
                                    <dl class="moto-card__details">
                                        <?php if (!empty($m['anno'])): ?>
                                            <dt>Anno</dt>
                                            <dd><?= (int)$m['anno'] ?></dd>
                                        <?php endif; ?>
                                        <?php if (!empty($m['cilindrata'])): ?>
                                            <dt>cc</dt>
                                            <dd><?= (int)$m['cilindrata'] ?></dd>
                                        <?php endif; ?>
                                        <?php if (!empty($m['targa'])): ?>
                                            <dt>Targa</dt>
                                            <dd><?= htmlspecialchars($m['targa']) ?></dd>
                                        <?php endif; ?>
                                    </dl>
                                    <div class="moto-card__actions">
                                        <button class="btn btn-secondary btn-small js-edit-moto"
                                            data-moto='<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>'>
                                            &#9999;&#65039; Modifica
                                        </button>
                                        <form method="POST" style="display:inline;"
                                            onsubmit="return confirm('Eliminare questa moto?')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="hidden" name="delete_moto_id" value="<?= (int)$m['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-small">
                                                &#128465;&#65039;
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Modal modifica -->
                        <div class="modal-overlay" id="editMotoModal" aria-hidden="true">
                            <div class="modal-box">
                                <h3 class="modal-box__title">Modifica Moto</h3>
                                <form method="POST" novalidate>
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <input type="hidden" name="edit_moto_id" id="edit_moto_id">
                                    <?php include 'includes/moto-fields.php'; ?>
                                    <div class="modal-box__actions">
                                        <button type="submit" class="btn btn-primary">Aggiorna</button>
                                        <button type="button" class="btn btn-secondary"
                                            id="closeEditModal">Annulla</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ==================== ORDINI ==================== -->
            <?php elseif ($section === 'orders'): ?>
                <div class="dashboard-section">
                    <h1 class="dashboard-section__title">I Miei Ordini</h1>
                    <p class="section-description">
                        Storico degli ordini con sconto professionista applicato
                    </p>

                    <?php if (!empty($bozze)): ?>
                        <!-- Bozze preventivi in attesa di pagamento -->
                        <h3 class="draft-section-title">Bozze salvate</h3>
                        <div class="draft-cards">
                            <?php foreach ($bozze as $b):
                                $scadenza = $b['scadenza_il'] ? new DateTime($b['scadenza_il']) : null;
                                $now      = new DateTime();
                                $giorniRimasti = $scadenza ? (int)$now->diff($scadenza)->format('%r%a') : null;
                                $expiryClass = '';
                                if ($giorniRimasti !== null) {
                                    if ($giorniRimasti <= 1) $expiryClass = 'draft-card__expiry--critical';
                                    elseif ($giorniRimasti <= 3) $expiryClass = 'draft-card__expiry--warning';
                                }
                                $draftJson = json_encode([
                                    'marca_moto'   => $b['marca_moto'],
                                    'modello_moto' => $b['modello_moto'],
                                    'cilindrata'   => $b['cilindrata'],
                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                            ?>
                                <div class="draft-card">
                                    <div class="draft-card__header">
                                        <span class="draft-card__moto"><?= htmlspecialchars(trim(($b['marca_moto'] ?? '') . ' ' . ($b['modello_moto'] ?? '') . ($b['cilindrata'] ? ' · ' . $b['cilindrata'] : '')), ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($scadenza && $giorniRimasti !== null): ?>
                                            <span class="draft-card__expiry <?= $expiryClass ?>">
                                                <?php if ($giorniRimasti <= 0): ?>Scade oggi<?php elseif ($giorniRimasti === 1): ?>Scade domani<?php else: ?>Scade tra <?= $giorniRimasti ?> giorni<?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="draft-card__route">
                                        <?= htmlspecialchars($b['indirizzo_ritiro'] ?? '—', ENT_QUOTES, 'UTF-8') ?> &rarr;
                                        <?= htmlspecialchars($b['indirizzo_consegna'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <div class="draft-card__footer">
                                        <strong class="draft-card__price">&euro;<?= number_format((float)($b['prezzo_finale'] ?? 0), 2, ',', '.') ?></strong>
                                        <button class="btn btn-primary btn-sm"
                                            onclick="window.resumeDraft(<?= (int)$b['id'] ?>, <?= htmlspecialchars($draftJson, ENT_QUOTES, 'UTF-8') ?>)">
                                            Completa pagamento &rarr;
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($ordini)): ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">&#128230;</div>
                            <h3 class="empty-state__title">Nessun ordine trovato</h3>
                            <a href="/" class="btn btn-primary">Richiedi un preventivo</a>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <th>Totale</th>
                                        <th>Sconto</th>
                                        <th>Stato</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ordini as $o): ?>
                                        <tr>
                                            <td>#<?= (int)$o['id'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($o['creato_il'])) ?></td>
                                            <td>&euro;<?= number_format($o['totale'], 2, ',', '.') ?></td>
                                            <td>
                                                <span class="badge badge--success">
                                                    <?= number_format($sconto, 0) ?>%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge--<?= htmlspecialchars($o['stato']) ?>">
                                                    <?= ucfirst(htmlspecialchars($o['stato'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="/js/modules/nav.js"></script>
    <?php if ($section === 'orders' && !empty($bozze)): ?>
        <!-- Quote modal per riprendere bozze -->
        <?php include 'includes/quote-modal.php'; ?>
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            window.STRIPE_PUBLIC_KEY = <?= json_encode($stripePk, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            window.QUOTE_USER_DATA = <?= json_encode($quoteUserData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        </script>
        <script src="/js/modules/quote-modal.js"></script>
        <script>
            if (typeof window.resumeDraft !== 'function') {
                console.error('[dashboard-pro] quote-modal.js non ha definito window.resumeDraft.');
                window.resumeDraft = function(draftId) {
                    alert('Impossibile aprire il pagamento: il sistema modale non si è caricato correttamente. Ricarica la pagina e riprova.');
                };
            }
        </script>
    <?php endif; ?>
    <script>
        (function() {
            const addCard = document.getElementById('addMotoCard');
            const btnAdd = document.getElementById('btnAddMoto');
            const btnCancel = document.getElementById('btnCancelMoto');
            if (btnAdd) btnAdd.addEventListener('click', () => addCard.style.display = 'block');
            if (btnCancel) btnCancel.addEventListener('click', () => addCard.style.display = 'none');

            const modal = document.getElementById('editMotoModal');
            const closeModal = document.getElementById('closeEditModal');

            document.querySelectorAll('.js-edit-moto').forEach(btn => {
                btn.addEventListener('click', () => {
                    const m = JSON.parse(btn.dataset.moto);
                    document.getElementById('edit_moto_id').value = m.id;
                    ['marca', 'modello', 'anno', 'cilindrata', 'targa', 'colore', 'note'].forEach(f => {
                        const el = modal.querySelector('[name="' + f + '"]');
                        if (el) el.value = m[f] ?? '';
                    });
                    if (modal) {
                        modal.setAttribute('aria-hidden', 'false');
                        modal.style.display = 'flex';
                    }
                });
            });

            if (closeModal) {
                closeModal.addEventListener('click', () => {
                    modal.setAttribute('aria-hidden', 'true');
                    modal.style.display = 'none';
                });
            }
        }());
    </script>
    <script>
        var avatarInput = document.getElementById('avatarFileInput');
        if (avatarInput) {
            avatarInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    document.getElementById('avatarUploadForm').submit();
                }
            });
        }
    </script>
</body>

</html>