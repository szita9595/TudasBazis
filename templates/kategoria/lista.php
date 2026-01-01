<?php
/**
 * Kategória lista template
 */
// Az e() függvény a helpers.php-ból elérhető
?>

<div class="breadcrumb">
    <a href="/">Kezdőoldal</a>
    <?php foreach ($breadcrumb ?? [] as $bc): ?>
    <span>»</span> <a href="/kategoria/<?= e($bc->slug) ?>"><?= e($bc->nev) ?></a>
    <?php endforeach; ?>
</div>

<h1 class="page-title"><?= e($aktualisKategoria->nev) ?></h1>

<?php if (!empty($alkategoriak)): ?>
<div style="margin-bottom: 15px;">
    <strong>Alkategóriák:</strong>
    <?php foreach ($alkategoriak as $alkat): ?>
    <a href="/kategoria/<?= e($kategoria->slug) ?>/<?= e($alkat->slug) ?>"><?= e($alkat->nev) ?></a>
    <?php if ($alkat !== end($alkategoriak)): ?> | <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="kerdes-section">
    <div class="kerdes-section-header">
        <span class="kerdes-section-title">Kérdések (<?= $osszesKerdes ?> db)</span>
    </div>
    <div class="kerdes-lista">
        <?php if (empty($kerdesek)): ?>
        <div class="kerdes-item">
            <span style="color: #888;">Még nincs kérdés ebben a kategóriában.</span>
        </div>
        <?php else: ?>
        <?php foreach ($kerdesek as $kerdes): ?>
        <div class="kerdes-item">
            <div class="kerdes-ikon">?</div>
            <div class="kerdes-tartalom">
                <div class="kerdes-cim">
                    <a href="/kerdes/<?= $kerdes->id ?>/<?= e($kerdes->slug) ?>">
                        <?= e($kerdes->cim) ?>
                    </a>
                </div>
                <div class="kerdes-meta">
                    <?= e($kerdes->kategoriaNev ?? '') ?>
                    · <?= e($kerdes->letrehozva ?? '') ?>
                </div>
            </div>
            <div class="kerdes-valaszok <?= $kerdes->valaszokSzama > 0 ? 'van-valasz' : '' ?>">
                <?= $kerdes->valaszokSzama ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($osszesKerdes > 20): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= ceil($osszesKerdes / 20); $i++): ?>
    <a href="?oldal=<?= $i ?>" class="<?= $i === $oldal ? 'aktiv' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
