<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/orders.php';

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
        $profileFields = array_intersect_key($_POST, array_flip(['nome','cognome','telefono','indirizzo','citta','cap','paese']));
        if (!empty($profileFields)) {
            updateUserProfile($user['id'], $_POST);
            if (empty($success)) $success = "Profilo aggiornato con successo!";
            $user = getCurrentUser();
        }
    } catch (Exception $e) {
        $error = "Errore nell'aggiornamento: " . $e->getMessage();
    }
}

// Carica ordini se necessario
$ordini = [];
if ($section === 'orders') {
    $ordini = getUserOrders($user['id']);
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

            <?php elseif ($section === 'orders'): ?>
                <!-- Sezione Ordini -->
                <div class="dashboard-section">
                    <h1>I Miei Ordini</h1>
                    <p class="section-description">Visualizza lo storico dei tuoi ordini</p>

                    <?php if (empty($ordini)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <h3>Nessun ordine trovato</h3>
                            <p>Non hai ancora effettuato ordini.</p>
                        </div>
                    <?php else: ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Ordine</th>
                                        <th>Data</th>
                                        <th>Totale</th>
                                        <th>Stato</th>
                                        <th>Metodo Pagamento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ordini as $ordine): ?>
                                        <tr>
                                            <td>#<?= $ordine['id'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($ordine['creato_il'])) ?></td>
                                            <td>&euro;<?= number_format($ordine['totale'], 2, ',', '.') ?></td>
                                            <td>
                                                <span class="badge badge-<?= $ordine['stato'] ?>">
                                                    <?= ucfirst($ordine['stato']) ?>
                                                </span>
                                            </td>
                                            <td><?= ucfirst($ordine['metodo_pagamento'] ?? 'N/A') ?></td>
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
            avatarInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    document.getElementById('avatarUploadForm').submit();
                }
            });
        }
    </script>
</body>

</html>