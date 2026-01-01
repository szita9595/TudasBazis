<?php
/**
 * Bejelentkezés és regisztráció template
 * 
 * Elérhető változók:
 * - $hiba: ?string - Hibaüzenet
 * - $adatok: array - Előző form adatok (regisztrációnál)
 * - $regisztracio: bool - Regisztrációs módban vagyunk-e
 * - $csrf_token: string
 */
// Az e() függvény a helpers.php-ból elérhető
$regisztracio = $regisztracio ?? false;
$adatok = $adatok ?? [];
?>

<div class="breadcrumb">
    <a href="/">Kezdőoldal</a> <span>»</span> Bejelentkezés, regisztráció
</div>

<h1 class="page-title">Belépés a TudásBázis weboldalon</h1>

<?php if ($hiba): ?>
<div class="form-error"><?= e($hiba) ?></div>
<?php endif; ?>

<div class="auth-container">
    <!-- Bejelentkezés -->
    <div class="auth-box">
        <div class="auth-box-header">
            Ha már van azonosítód, add meg<br>e-mail címed, jelszavad:
        </div>
        <div class="auth-box-content">
            <form method="post" action="/belepes">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="Email cím" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="jelszo" class="form-input" placeholder="Jelszó" required>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="emlekezz" value="1">
                        Belépve maradok
                    </label>
                </div>

                <div class="text-center">
                    <button type="submit" class="form-submit">Belépés</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Jelszó emlékeztető -->
    <div class="auth-box">
        <div class="auth-box-header">
            Ha elfelejtetted a jelszavad,<br>add meg az e-mail címed:
        </div>
        <div class="auth-box-content">
            <form method="post" action="/jelszo-emlekezteto">
                <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
                
                <div class="form-group">
                    <input type="email" name="email" class="form-input" placeholder="E-mail címed">
                </div>

                <div class="text-center">
                    <button type="submit" class="form-submit">Jelszó kérés</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Regisztráció -->
<div class="form-separator">
    <strong>Ha még nem regisztráltál:</strong>
</div>

<p style="text-align: center; margin-bottom: 20px; font-size: 12px;">
    Hozz létre egy regisztrációt, hogy kérdezhess, válaszolhass vagy értékelhess oldalunkon! Ezek
    mellett még más hasznos funkciók is elérhetővé válnak számodra, ha belépsz a fiókodba.
</p>

<div class="form-container" style="margin: 0 auto;">
    <form method="post" action="/regisztracio" class="form-box">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        
        <div class="form-box-content">
            <div class="form-group">
                <label class="form-label">Felhasználói neved:</label>
                <input type="text" name="felhasznalonev" class="form-input" 
                       value="<?= e($adatok['felhasznalonev'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">E-mail címed:</label>
                <input type="email" name="email" class="form-input" 
                       value="<?= e($adatok['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Jelszavad (kétszer add meg):</label>
                <input type="password" name="jelszo" class="form-input" required>
                <input type="password" name="jelszo_megerosites" class="form-input mt-10" required>
            </div>

            <div class="form-group">
                <label class="form-label">Látsszon a neved a kérdéseidnél és a válaszaidnál:</label>
                <div class="form-radio-group">
                    <label>
                        <input type="radio" name="nev_latszik" value="igen"> Igen
                    </label>
                    <label>
                        <input type="radio" name="nev_latszik" value="nem" checked> Nem
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="szabalyzat" value="1" required>
                    Megismertem és elfogadom a <a href="/szabalyzat">felhasználói szabályzatot</a> és az 
                    <a href="/adatvedelem">adatvédelmi irányelveket</a>, illetve a cookie-k használatát
                </label>
            </div>

            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="eletkor" value="1" required>
                    Nyilatkozom, hogy elmúltam 16 éves
                </label>
            </div>

            <div class="text-center">
                <button type="submit" class="form-submit">Regisztráció</button>
            </div>
        </div>
    </form>
</div>

<div class="auth-info">
    <p>A regisztrációkor az e-mail címedet, felhasználói nevedet, jelszavadat kell megadnod, illetve nyilatkoznod
    arról, hogy elmúltál már 16 éves és megértetted az oldal használatának szabályait.</p>
    
    <p>Miután regisztráltál, kérdéseket tudsz kiírni, válaszaiddal és értékelésekkel segíthetsz másoknak.</p>
    
    <ul>
        <li>Az e-mail címed a bejelentkezéshez és azonosításhoz fog kelleni</li>
        <li>A jelszavad biztonságosan, titkosítva tároljuk</li>
    </ul>
</div>
