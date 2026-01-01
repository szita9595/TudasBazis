<?php
/**
 * Kérdés lista template
 */
// Az e() függvény a helpers.php-ból elérhető
?>

<h1 class="page-title">
    <?php if ($szuro === 'megvalaszolatlan'): ?>
    Megválaszolatlan kérdések
    <?php else: ?>
    Legújabb kérdések
    <?php endif; ?>
</h1>

<div style="margin-bottom: 15px;">
    <a href="/kerdesek?szuro=legujabb" <?= $szuro === 'legujabb' ? 'style="font-weight:bold;"' : '' ?>>Legújabb</a> |
    <a href="/kerdesek?szuro=megvalaszolatlan" <?= $szuro === 'megvalaszolatlan' ? 'style="font-weight:bold;"' : '' ?>>Megválaszolatlan</a>
</div>

<div class="kerdes-section">
    <div class="kerdes-lista" style="border-top: 1px solid #ddd;">
        <?php if (empty($kerdesek)): ?>
        <div class="kerdes-item">
            <span style="color: #888;">Nincs találat.</span>
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
        <?php endif; ?>
    </div>
</div>
