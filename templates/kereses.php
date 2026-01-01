<?php
/**
 * Keresési eredmények template
 */
// Az e() függvény a helpers.php-ból elérhető
?>

<h1 class="page-title">Keresés</h1>

<form method="get" action="/kereses" class="kereses-form">
    <div class="kereses-input-container">
        <input type="text" name="q" class="kereses-input" 
               value="<?= e($kereses) ?>" 
               placeholder="Keresés a kérdésekben..." autofocus>
        <button type="submit" class="kereses-submit">Keresés</button>
    </div>
</form>

<?php if ($kereses): ?>
<div class="kereses-info">
    Találatok a(z) "<strong><?= e($kereses) ?></strong>" keresésre: <?= count($talalatok) ?> db
</div>
<?php endif; ?>

<?php if (!empty($talalatok)): ?>
<div class="kerdes-section">
    <div class="kerdes-lista" style="border-top: 1px solid #ddd;">
        <?php foreach ($talalatok as $kerdes): ?>
        <div class="kerdes-item">
            <div class="kerdes-ikon">?</div>
            <div class="kerdes-tartalom">
                <div class="kerdes-cim">
                    <a href="/kerdes/<?= $kerdes->id ?>/<?= e($kerdes->slug) ?>">
                        <?= e($kerdes->cim) ?>
                    </a>
                </div>
                <div class="kerdes-meta">
                    <a href="/kategoria/<?= e($kerdes->kategoriaSlug ?? '') ?>">
                        <?= e($kerdes->kategoriaNev ?? '') ?>
                    </a>
                    · <?= e($kerdes->letrehozva ?? '') ?>
                </div>
            </div>
            <div class="kerdes-valaszok <?= $kerdes->valaszokSzama > 0 ? 'van-valasz' : '' ?>">
                <?= $kerdes->valaszokSzama ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php elseif ($kereses): ?>
<p style="color: #888; text-align: center; padding: 30px;">
    Nincs találat a megadott keresésre.
</p>
<?php endif; ?>
