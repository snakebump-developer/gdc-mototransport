<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';
require_once __DIR__ . '/../src/motorcycles.php';

requireLogin();

$user = getCurrentUser();
$section = $_GET['section'] ?? 'profile';
$success = '';
$error = '';
$pageTitle = 'Dashboard - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/dashboard.css'];

// Gestione aggiornamento profilo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $section === 'profile') {
    try {
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
            // Rimuovi vecchio avatar
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
        // Aggiornamento dati profilo (solo se ci sono campi POST oltre csrf/action)
        $profileFields = array_intersect_key($_POST, array_flip(['nome', 'cognome', 'telefono', 'indirizzo', 'citta', 'cap', 'paese']));
        if (!empty($profileFields)) {
            updateUserProfile($user['id'], $_POST);
            if (empty($success)) $success = "Profilo aggiornato con successo!";
            $user = getCurrentUser();
        }
    } catch (Exception $e) {
        $error = "Errore nell'aggiornamento: " . $e->getMessage();
    }
}

// Carica dati della sezione attiva
$preventivi = [];
$moto = [];
if ($section === 'orders') {
    $preventivi = getUserPreventivi($user['id']);
} elseif ($section === 'motorcycles') {
    $moto = getUserMotorcycles((int)$user['id']);
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
        <?php include 'includes/sidebar-dashboard.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <?php include 'includes/alerts.php'; ?>

            <?php if ($section === 'profile'): ?>
                <!-- Sezione Profilo -->
                <div class="dashboard-section">
                    <h1>Il Mio Profilo</h1>
                    <p class="section-description">Gestisci le tue informazioni personali</p>

                    <!-- Card Avatar -->
                    <div class="avatar-card">
                        <div class="avatar-preview-wrapper">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="/<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Avatar" class="avatar-preview">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <?= htmlspecialchars(strtoupper(substr($user['nome'] ?? $user['username'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="avatar-upload-area">
                            <h3>Foto Profilo</h3>
                            <p class="avatar-hint">JPG, PNG, GIF o WebP &middot; Max 2&nbsp;MB</p>
                            <div class="avatar-actions">
                                <form method="POST" enctype="multipart/form-data" class="avatar-form" id="avatarUploadForm">
                                    <label class="btn btn-secondary btn-sm avatar-upload-btn">
                                        Carica immagine
                                        <input type="file" name="avatar"
                                            accept="image/jpeg,image/png,image/gif,image/webp"
                                            id="avatarFileInput" style="display:none">
                                    </label>
                                </form>
                                <?php if (!empty($user['avatar'])): ?>
                                    <form method="POST" class="avatar-form">
                                        <button type="submit" name="remove_avatar" value="1"
                                            class="btn btn-ghost btn-sm">Rimuovi</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="profile-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <small>Username non modificabile</small>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                <small>Email non modificabile</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="cognome">Cognome</label>
                                <input type="text" id="cognome" name="cognome" value="<?= htmlspecialchars($user['cognome'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Telefono</label>
                                <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="indirizzo">Indirizzo</label>
                                <input type="text" id="indirizzo" name="indirizzo" value="<?= htmlspecialchars($user['indirizzo'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="citta">Città</label>
                                <input type="text" id="citta" name="citta" value="<?= htmlspecialchars($user['citta'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="cap">CAP</label>
                                <input type="text" id="cap" name="cap" value="<?= htmlspecialchars($user['cap'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="paese">Paese</label>
                                <input type="text" id="paese" name="paese" value="<?= htmlspecialchars($user['paese'] ?? '') ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Salva Modifiche</button>
                    </form>
                </div>

            <?php elseif ($section === 'motorcycles'): ?>
                <!-- Sezione Le Mie Moto -->
                <div class="dashboard-section">
                    <h1>Le Mie Moto</h1>
                    <p class="section-description">Le moto associate ai tuoi preventivi di trasporto</p>

                    <?php if (empty($moto)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🏍️</div>
                            <h3>Nessuna moto salvata</h3>
                            <p>Le moto che inserisci nei preventivi vengono salvate automaticamente qui. <a href="/" class="link">Richiedi un trasporto →</a></p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Marca</th>
                                        <th>Modello</th>
                                        <th>Cilindrata</th>
                                        <th>Anno</th>
                                        <th>Targa</th>
                                        <th>Aggiunta il</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($moto as $m): ?>
                                        <tr>
                                            <td><strong>#<?= (int)$m['id'] ?></strong></td>
                                            <td><?= htmlspecialchars($m['marca'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($m['modello'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= $m['cilindrata'] ? htmlspecialchars($m['cilindrata'], ENT_QUOTES, 'UTF-8') . ' cc' : '—' ?></td>
                                            <td><?= $m['anno'] ? (int)$m['anno'] : '—' ?></td>
                                            <td><?= $m['targa'] ? htmlspecialchars(strtoupper($m['targa']), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                            <td><?= date('d/m/Y', strtotime($m['creato_il'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($section === 'orders'): ?>
                <!-- Sezione Trasporti / Preventivi -->
                <div class="dashboard-section">
                    <h1>I Miei Trasporti</h1>
                    <p class="section-description">Storico dei tuoi preventivi e trasporti moto</p>

                    <?php if (empty($preventivi)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🏍️</div>
                            <h3>Nessun trasporto trovato</h3>
                            <p>Non hai ancora richiesto un preventivo. <a href="/" class="link">Richiedi ora →</a></p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Data richiesta</th>
                                        <th>Moto</th>
                                        <th>Tragitto</th>
                                        <th>Data ritiro</th>
                                        <th>Importo</th>
                                        <th>Stato ordine</th>
                                        <th>Pagamento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($preventivi as $p):
                                        $pgStato = $p['pagamento_stato'] ?? 'non_pagato';
                                        $pgBadge = [
                                            'non_pagato' => ['badge-pending',  'Non pagato'],
                                            'pagato'     => ['badge-completed', 'Pagato'],
                                            'fallito'    => ['badge-cancelled', 'Fallito'],
                                            'rimborsato' => ['badge-processing', 'Rimborsato'],
                                        ][$pgStato] ?? ['badge-pending', ucfirst($pgStato)];

                                        $ordineStati = [
                                            'inviato'      => ['badge-pending',   'Inviato'],
                                            'confermato'   => ['badge-completed', 'Confermato'],
                                            'in_lavorazione' => ['badge-processing', 'In lavorazione'],
                                            'completato'   => ['badge-completed', 'Completato'],
                                            'annullato'    => ['badge-cancelled', 'Annullato'],
                                            'bozza'        => ['badge-pending',   'Bozza'],
                                        ];
                                        $ordineBadge = $ordineStati[$p['stato'] ?? 'bozza'] ?? ['badge-pending', ucfirst($p['stato'] ?? '—')];
                                    ?>
                                        <tr>
                                            <td><strong>#<?= $p['id'] ?></strong></td>
                                            <td><?= date('d/m/Y', strtotime($p['creato_il'])) ?></td>
                                            <td><?= htmlspecialchars(trim(($p['marca_moto'] ?? '') . ' ' . ($p['modello_moto'] ?? ''))) ?></td>
                                            <td class="td-wrap" style="font-size:0.8rem;">
                                                <?= htmlspecialchars($p['indirizzo_ritiro'] ?? '—') ?>
                                                <span style="color:var(--text-secondary)"> → </span>
                                                <?= htmlspecialchars($p['indirizzo_consegna'] ?? '—') ?>
                                            </td>
                                            <td><?= $p['data_ritiro'] ? date('d/m/Y', strtotime($p['data_ritiro'])) : '—' ?></td>
                                            <td><strong>&euro;<?= number_format((float)($p['prezzo_finale'] ?? 0), 2, ',', '.') ?></strong></td>
                                            <td><span class="badge <?= $ordineBadge[0] ?>"><?= $ordineBadge[1] ?></span></td>
                                            <td>
                                                <span class="badge <?= $pgBadge[0] ?>"><?= $pgBadge[1] ?></span>
                                                <?php if (!empty($p['pagamento_receipt'])): ?>
                                                    <br><a href="<?= htmlspecialchars($p['pagamento_receipt'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="font-size:0.75rem;color:var(--primary-color);">Ricevuta →</a>
                                                <?php endif; ?>
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
    <script src="/js/modules/forms.js"></script>
    <script>
        // Auto-submit form avatar quando si seleziona un file
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