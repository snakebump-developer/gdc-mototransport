<?php
require_once __DIR__ . '/../src/auth.php';

if (isLogged()) {
    header('Location: /');
    exit;
}

$error   = '';
$success = '';
$pageTitle  = 'Registrazione - MotoTransport';
$noFontAwesome = true;
$extraCss = ['css/modules/auth.css'];

// Tipo selezionato (ripopolato in caso di errore)
$tipoPost = (isset($_POST['tipo_account']) && $_POST['tipo_account'] === 'professional')
    ? 'professional' : 'privato';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tipo  = $tipoPost;
        $extra = [
            'nome'               => trim($_POST['nome']     ?? ''),
            'cognome'            => trim($_POST['cognome']  ?? ''),
            'telefono'           => trim($_POST['telefono'] ?? ''),
            'gdpr_accettato'     => isset($_POST['gdpr'])      ? 1 : 0,
            'marketing_accettato' => isset($_POST['marketing']) ? 1 : 0,
        ];

        if ($tipo === 'professional') {
            $extra['ragione_sociale']        = trim($_POST['ragione_sociale']        ?? '');
            $extra['partita_iva']            = trim($_POST['partita_iva']            ?? '');
            $extra['codice_fiscale_azienda'] = trim($_POST['codice_fiscale_azienda'] ?? '');
            $extra['pec']                    = trim($_POST['pec']                    ?? '');
            $extra['codice_sdi']             = trim($_POST['codice_sdi']             ?? '');
            $extra['tipo_attivita']          = trim($_POST['tipo_attivita']          ?? '');
        }

        registerUser($_POST['username'], $_POST['email'], $_POST['password'], $tipo, $extra);
        $success = "Registrazione completata! Ora puoi accedere.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Helper per ripopolare i campi
function old(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? htmlspecialchars($_POST[$key]) : $default;
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <?php include 'includes/head.php'; ?>
</head>

<body>
    <div class="auth-container">
        <div class="auth-box auth-box--register" id="authBox">
            <div class="auth-header">
                <h2>Registrati</h2>
                <p>Crea il tuo account gratuito</p>
            </div>

            <?php include 'includes/alerts.php'; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    Registrazione completata! <a href="/login">Vai al login</a>
                </div>
            <?php else: ?>

                <!-- ===== SELEZIONE TIPO ACCOUNT ===== -->
                <div class="account-type-selector">
                    <button type="button"
                        class="account-type-btn <?= $tipoPost === 'privato' ? 'active' : '' ?>"
                        data-type="privato">
                        <span class="account-type-icon">👤</span>
                        <span class="account-type-label">Privato</span>
                        <span class="account-type-desc">Uso personale</span>
                    </button>
                    <button type="button"
                        class="account-type-btn <?= $tipoPost === 'professional' ? 'active' : '' ?>"
                        data-type="professional">
                        <span class="account-type-icon">🏢</span>
                        <span class="account-type-label">Professionista B2B</span>
                        <span class="account-type-desc">Azienda / P.IVA</span>
                    </button>
                </div>

                <form method="POST" class="auth-form" id="registerForm">
                    <input type="hidden" name="tipo_account" id="tipoAccount" value="<?= htmlspecialchars($tipoPost) ?>">

                    <!-- ===== DATI ACCESSO ===== -->
                    <fieldset class="form-section">
                        <legend>Dati di accesso</legend>

                        <div class="form-group">
                            <label for="username">Username <span class="required">*</span></label>
                            <input type="text" id="username" name="username" required
                                pattern="[a-zA-Z0-9_]{3,20}"
                                title="3-20 caratteri alfanumerici"
                                value="<?= old('username') ?>">
                            <small>3-20 caratteri alfanumerici e underscore</small>
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required
                                value="<?= old('email') ?>">
                        </div>

                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" id="password" name="password" required
                                minlength="8"
                                pattern="(?=.*[A-Za-z])(?=.*\d).{8,}"
                                title="Minimo 8 caratteri, almeno 1 lettera e 1 numero">
                            <small>Minimo 8 caratteri, almeno 1 lettera e 1 numero</small>
                        </div>
                    </fieldset>

                    <!-- ===== DATI PERSONALI ===== -->
                    <fieldset class="form-section">
                        <legend>Dati personali</legend>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nome">Nome <span class="required professional-required" style="display:none">*</span></label>
                                <input type="text" id="nome" name="nome" autocomplete="given-name"
                                    value="<?= old('nome') ?>">
                            </div>
                            <div class="form-group">
                                <label for="cognome">Cognome <span class="required professional-required" style="display:none">*</span></label>
                                <input type="text" id="cognome" name="cognome" autocomplete="family-name"
                                    value="<?= old('cognome') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Telefono</label>
                            <input type="tel" id="telefono" name="telefono" autocomplete="tel"
                                value="<?= old('telefono') ?>">
                        </div>
                    </fieldset>

                    <!-- ===== DATI AZIENDALI (solo professional) ===== -->
                    <fieldset class="form-section professional-section" id="professionalSection"
                        style="<?= $tipoPost === 'professional' ? '' : 'display:none' ?>">
                        <legend>Dati aziendali</legend>

                        <div class="form-group">
                            <label for="ragione_sociale">Ragione sociale <span class="required">*</span></label>
                            <input type="text" id="ragione_sociale" name="ragione_sociale"
                                value="<?= old('ragione_sociale') ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="partita_iva">Partita IVA <span class="required">*</span></label>
                                <input type="text" id="partita_iva" name="partita_iva"
                                    pattern="\d{11}" maxlength="11"
                                    title="11 cifre numeriche"
                                    placeholder="12345678901"
                                    value="<?= old('partita_iva') ?>">
                                <small>11 cifre numeriche</small>
                            </div>
                            <div class="form-group">
                                <label for="codice_fiscale_azienda">Codice fiscale azienda</label>
                                <input type="text" id="codice_fiscale_azienda" name="codice_fiscale_azienda"
                                    maxlength="16" style="text-transform:uppercase"
                                    value="<?= old('codice_fiscale_azienda') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="pec">PEC</label>
                                <input type="email" id="pec" name="pec"
                                    placeholder="azienda@pec.it"
                                    value="<?= old('pec') ?>">
                            </div>
                            <div class="form-group">
                                <label for="codice_sdi">Codice SDI</label>
                                <input type="text" id="codice_sdi" name="codice_sdi"
                                    maxlength="7" style="text-transform:uppercase"
                                    placeholder="XXXXXXX"
                                    value="<?= old('codice_sdi') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tipo_attivita">Tipo di attività</label>
                            <select id="tipo_attivita" name="tipo_attivita">
                                <option value="">-- Seleziona --</option>
                                <?php
                                $tipiAttivita = [
                                    'concessionario'  => 'Concessionario di moto',
                                    'officina'        => 'Officina / Carrozzeria',
                                    'dealer'          => 'Dealer / Rivenditore',
                                    'broker'          => 'Broker assicurativo',
                                    'agenzia_trasporti' => 'Agenzia trasporti',
                                    'importatore'     => 'Importatore / Esportatore',
                                    'altro'           => 'Altro',
                                ];
                                foreach ($tipiAttivita as $val => $label):
                                    $sel = (old('tipo_attivita') === $val) ? 'selected' : '';
                                ?>
                                    <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </fieldset>

                    <!-- ===== PRIVACY ===== -->
                    <fieldset class="form-section">
                        <legend>Privacy e consensi</legend>

                        <div class="form-group form-check">
                            <label class="check-label">
                                <input type="checkbox" name="gdpr" id="gdpr" required
                                    <?= isset($_POST['gdpr']) ? 'checked' : '' ?>>
                                <span>Accetto la <a href="/privacy-policy" target="_blank">Privacy Policy</a> e il
                                    trattamento dei miei dati personali ai sensi del GDPR. <span class="required">*</span></span>
                            </label>
                        </div>

                        <div class="form-group form-check">
                            <label class="check-label">
                                <input type="checkbox" name="marketing" id="marketing"
                                    <?= isset($_POST['marketing']) ? 'checked' : '' ?>>
                                <span>Acconsento a ricevere comunicazioni commerciali e promozionali (facoltativo).</span>
                            </label>
                        </div>
                    </fieldset>

                    <button type="submit" class="btn btn-primary btn-block">Registrati</button>
                </form>

            <?php endif; ?>

            <div class="auth-footer">
                <p>Hai già un account? <a href="/login">Accedi</a></p>
                <p><a href="/">Torna alla home</a></p>
            </div>
        </div>
    </div>

    <?php include 'includes/whatsapp-button.php'; ?>

    <script>
        (function() {
            const btns = document.querySelectorAll('.account-type-btn');
            const input = document.getElementById('tipoAccount');
            const proSec = document.getElementById('professionalSection');
            const proReq = document.querySelectorAll('.professional-required');
            const proInputs = proSec ? proSec.querySelectorAll('input, select') : [];
            const nomeEl = document.getElementById('nome');
            const cognomeEl = document.getElementById('cognome');
            const ragSocEl = document.getElementById('ragione_sociale');
            const pivaEl = document.getElementById('partita_iva');
            const authBox = document.getElementById('authBox');

            function setType(tipo) {
                input.value = tipo;

                btns.forEach(b => b.classList.toggle('active', b.dataset.type === tipo));

                const isPro = tipo === 'professional';
                if (proSec) proSec.style.display = isPro ? '' : 'none';

                // Required dinamici
                proReq.forEach(el => el.style.display = isPro ? '' : 'none');
                if (nomeEl) nomeEl.required = isPro;
                if (cognomeEl) cognomeEl.required = isPro;
                if (ragSocEl) ragSocEl.required = isPro;
                if (pivaEl) pivaEl.required = isPro;

                // Larghezza box
                if (authBox) authBox.classList.toggle('auth-box--wide', isPro);
            }

            btns.forEach(btn => {
                btn.addEventListener('click', () => setType(btn.dataset.type));
            });

            // Inizializzazione (ripopolamento post-errore)
            setType(input.value || 'privato');
        })();
    </script>
</body>

</html>