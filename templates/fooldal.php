<?php
/**
 * Főoldal template
 * 
 * Elérhető változók:
 * - $kategoriak: array - Kategóriák
 * - $kerdesekKategoriankent: array - Kérdések kategóriánként
 * - $statisztikak: array - Oldal statisztikák
 * - $felhasznalo: ?FelhasznaloEntity
 */
// Az e() függvény a helpers.php-ból elérhető
?>

<h1 class="page-title">Gyakori kérdések - Tedd fel a kérdésed!</h1>

<div style="display: flex; gap: 20px;">
    <!-- Bal oldal: Népszerű témák és kérdések -->
    <div style="flex: 2;">
        <!-- Népszerű témák (tag cloud) -->
        <div class="tag-cloud">
            <div class="tag-cloud-title">Most népszerű témák:</div>
            <div class="tag-cloud-lista">
                <?php 
                $temak = ['Ajándék', 'Amerikai', 'Egyesült Államok', 'Barátnő', 'Beszélgetés', 'Budapest', 'Buli', 'Család', 'Depresszió', 'Egyetem', 'Élet', 'Európa', 'Facebook', 'Félelem', 'Férfi', 'Film', 'Gyerek', 'Háború', 'Ismerkedés', 'Játék'];
                foreach ($temak as $tema): ?>
                <a href="/kereses?q=<?= urlencode($tema) ?>"><?= e($tema) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Kérdések kategóriánként -->
        <?php foreach ($kerdesekKategoriankent ?? [] as $katId => $data): ?>
        <?php $kategoria = $data['kategoria']; $kerdesek = $data['kerdesek']; ?>
        <div class="kerdes-section">
            <div class="kerdes-section-header">
                <span class="kerdes-section-title">
                    <?= e($kategoria->nev) ?> téma:
                </span>
                <a href="/kategoria/<?= e($kategoria->slug) ?>" class="kerdes-section-more">
                    <?= e($kategoria->nev) ?> - További kérdések
                </a>
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
                            <a href="/kategoria/<?= e($kerdes->kategoriaSlug ?? '') ?>">
                                <?= e($kategoria->nev) ?>
                            </a>
                            <?php if ($kerdes->kategoriaNev && $kerdes->kategoriaNev !== $kategoria->nev): ?>
                            » <?= e($kerdes->kategoriaNev) ?>
                            <?php endif; ?>
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
        <?php endforeach; ?>
    </div>

    <!-- Jobb oldal: Statisztikák -->
    <div style="flex: 1;">
        <div class="stat-box">
            <div class="stat-box-title">TUDÁSBÁZIS</div>
            <div class="stat-box-subtitle">2024 óta az interneten!</div>
            
            <div class="stat-item">
                <span class="stat-number"><?= number_format($statisztikak['osszes_kerdes'] ?? 0, 0, ',', ' ') ?></span> kérdés
            </div>
            <div class="stat-item">
                naponta <span class="stat-number">100+</span> új kérdés
            </div>
            
            <div class="stat-box-cta">
                <div class="stat-box-cta-title">Kérdésed van?</div>
                <div class="stat-box-cta-text">Jó helyen jársz!</div>
            </div>
        </div>
    </div>
</div>
