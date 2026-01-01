<?php
/**
 * Új kérdés template
 */
// Az e() függvény a helpers.php-ból elérhető
?>

<h1 class="page-title">Új kérdés feltevése</h1>

<?php if ($hiba): ?>
<div class="form-error"><?= e($hiba) ?></div>
<?php endif; ?>

<form method="post" action="/uj-kerdes" class="form-box">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    
    <div class="form-box-content">
        <div class="form-group">
            <label class="form-label">Kategória:</label>
            <select name="kategoria_id" class="form-select" required>
                <option value="">-- Válassz kategóriát --</option>
                <?php foreach ($osszesKategoria ?? [] as $kat): ?>
                <option value="<?= $kat->id ?>" <?= ($adatok['kategoria_id'] ?? 0) == $kat->id ? 'selected' : '' ?>>
                    <?= $kat->szuloId ? '&nbsp;&nbsp;&nbsp;' : '' ?><?= e($kat->nev) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Kérdésed címe (min. 10 karakter):</label>
            <input type="text" name="cim" class="form-input" 
                   value="<?= e($adatok['cim'] ?? '') ?>" 
                   placeholder="Pl.: Hogyan lehet gyorsan megtanulni programozni?"
                   minlength="10" maxlength="255" required>
        </div>

        <div class="form-group">
            <label class="form-label">Kérdésed kifejtése (min. 20 karakter):</label>
            <textarea name="tartalom" class="valasz-textarea" 
                      placeholder="Fejtsd ki részletesen a kérdésedet..."
                      minlength="20" required><?= e($adatok['tartalom'] ?? '') ?></textarea>
        </div>

        <div class="text-center">
            <button type="submit" class="form-submit">Kérdés feltevése</button>
        </div>
    </div>
</form>

<div class="auth-info">
    <p><strong>Tippek a jó kérdéshez:</strong></p>
    <ul>
        <li>Fogalmazd meg világosan, hogy mire keresel választ</li>
        <li>Add meg a releváns részleteket</li>
        <li>Válaszd ki a megfelelő kategóriát</li>
    </ul>
</div>
