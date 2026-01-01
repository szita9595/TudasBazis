<?php
/**
 * Kérdés részletek template
 */
// Az e() függvény a helpers.php-ból elérhető
?>

<div class="breadcrumb">
    <a href="/">Kezdőoldal</a>
    <?php foreach ($breadcrumb ?? [] as $bc): ?>
    <span>»</span> <a href="/kategoria/<?= e($bc->slug) ?>"><?= e($bc->nev) ?></a>
    <?php endforeach; ?>
</div>

<!-- Kérdés -->
<div class="kerdes-reszletek">
    <div class="kerdes-reszletek-header">
        <h1 class="kerdes-reszletek-cim"><?= e($kerdes->cim) ?></h1>
        <div class="kerdes-reszletek-meta">
            Kérdezte: <strong><?= e($kerdes->felhasznalonev ?? 'Névtelen') ?></strong>
            · <?= e($kerdes->letrehozva) ?>
            · <?= $kerdes->megtekintesek ?> megtekintés
        </div>
    </div>
    <div class="kerdes-reszletek-tartalom">
        <?= nl2br(e($kerdes->tartalom)) ?>
    </div>
</div>

<!-- Válaszok -->
<div class="valaszok-section">
    <div class="valaszok-header">
        Válaszok (<?= count($valaszok) ?>)
    </div>

    <?php if (empty($valaszok)): ?>
    <div class="valasz-item">
        <p style="color: #888;">Még nincs válasz erre a kérdésre. Légy te az első!</p>
    </div>
    <?php else: ?>
    <?php foreach ($valaszok as $valasz): ?>
    <div class="valasz-item">
        <div class="valasz-header">
            <div class="valasz-szerzo">
                <div class="valasz-szerzo-avatar">👤</div>
                <div class="valasz-szerzo-info">
                    <div class="valasz-szerzo-nev">
                        <a href="/profil/<?= $valasz->felhasznaloId ?>"><?= e($valasz->felhasznalonev ?? 'Névtelen') ?></a>
                    </div>
                    <div class="valasz-szerzo-reputacio">
                        <?= number_format($valasz->felhasznaloReputacio ?? 0, 1) ?>% hasznos
                    </div>
                </div>
            </div>
            
            <?php if ($felhasznalo): ?>
            <div class="valasz-szavazatok">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                <button type="button" class="szavazat-gomb hasznos" 
                        data-valasz-id="<?= $valasz->id ?>" data-irany="hasznos">
                    👍 Hasznos (<?= $valasz->hasznosSzavazat ?>)
                </button>
                <button type="button" class="szavazat-gomb nem-hasznos"
                        data-valasz-id="<?= $valasz->id ?>" data-irany="nem_hasznos">
                    👎 (<?= $valasz->nemHasznosSzavazat ?>)
                </button>
            </div>
            <?php else: ?>
            <div class="szavazat-szam">
                👍 <?= $valasz->hasznosSzavazat ?> · 👎 <?= $valasz->nemHasznosSzavazat ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="valasz-tartalom">
            <?= nl2br(e($valasz->tartalom)) ?>
        </div>
        
        <div class="valasz-datum">
            <?= e($valasz->letrehozva) ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Válasz form -->
<?php if ($felhasznalo): ?>
<div class="valasz-form-section">
    <div class="valasz-form-header">Írd meg a válaszod:</div>
    <form class="valasz-form" method="post" action="/valasz/<?= $kerdes->id ?>">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="kerdes_id" value="<?= $kerdes->id ?>">
        
        <div class="form-group">
            <textarea name="tartalom" class="valasz-textarea" 
                      placeholder="Írj egy hasznos választ..." required></textarea>
        </div>
        
        <div class="text-right">
            <button type="submit" class="form-submit">Válasz küldése</button>
        </div>
    </form>
</div>
<?php else: ?>
<div class="valasz-form-section">
    <div class="valasz-form-header">Válasz írása</div>
    <div style="padding: 20px; text-align: center;">
        <p><a href="/belepes">Jelentkezz be</a> a válaszadáshoz!</p>
    </div>
</div>
<?php endif; ?>
