<?php

/**
 * Partial: campi del form moto (riutilizzato in dashboard utente, dashboard-pro e modal modifica).
 * Nessuna variabile obbligatoria — i valori vengono pre-compilati via JS nel modal.
 */
?>
<div class="form-row">
    <div class="form-group">
        <label class="form-group__label" for="mf-marca">Marca *</label>
        <input class="form-group__input" type="text" id="mf-marca" name="marca"
            required maxlength="50" autocomplete="off">
    </div>
    <div class="form-group">
        <label class="form-group__label" for="mf-modello">Modello *</label>
        <input class="form-group__input" type="text" id="mf-modello" name="modello"
            required maxlength="80" autocomplete="off">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-group__label" for="mf-anno">Anno</label>
        <input class="form-group__input" type="number" id="mf-anno" name="anno"
            min="1900" max="<?= (int)date('Y') + 1 ?>" step="1" autocomplete="off">
    </div>
    <div class="form-group">
        <label class="form-group__label" for="mf-cc">Cilindrata (cc)</label>
        <input class="form-group__input" type="number" id="mf-cc" name="cilindrata"
            min="50" max="3000" step="1" autocomplete="off">
    </div>
</div>
<div class="form-row">
    <div class="form-group">
        <label class="form-group__label" for="mf-targa">Targa</label>
        <input class="form-group__input" type="text" id="mf-targa" name="targa"
            maxlength="10" style="text-transform:uppercase" autocomplete="off"
            placeholder="AB123CD">
    </div>
    <div class="form-group">
        <label class="form-group__label" for="mf-colore">Colore</label>
        <input class="form-group__input" type="text" id="mf-colore" name="colore"
            maxlength="30" autocomplete="off">
    </div>
</div>
<div class="form-group">
    <label class="form-group__label" for="mf-note">Note</label>
    <textarea class="form-group__input form-group__textarea"
        id="mf-note" name="note" maxlength="500" rows="2"></textarea>
</div>