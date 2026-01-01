<?php
/**
 * Profil oldal template
 */
// Az e() függvény a helpers.php-ból elérhető
?>

<div class="breadcrumb">
    <a href="/">Kezdőoldal</a> <span>»</span> Profil
</div>

<div class="profil-header">
    <div class="profil-avatar">👤</div>
    <div class="profil-info">
        <h1><?= e($profilFelhasznalo->felhasznalonev) ?></h1>
        <div class="profil-stat">Regisztráció: <?= e($profilFelhasznalo->letrehozva ?? 'Ismeretlen') ?></div>
        <div class="profil-stat">Válaszok száma: <strong><?= $valaszokSzama ?></strong></div>
        <div class="profil-reputacio">
            Hasznossági arány: <?= number_format($profilFelhasznalo->reputacioSzazalek, 1) ?>%
        </div>
    </div>
</div>

<!-- Felhasználó kérdései -->
<div class="kerdes-section">
    <div class="kerdes-section-header">
        <span class="kerdes-section-title">Saját kérdései</span>
    </div>
    <div class="kerdes-lista">
        <?php if (empty($kerdesek)): ?>
        <div class="kerdes-item">
            <span style="color: #888;">Még nincs kérdése.</span>
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
