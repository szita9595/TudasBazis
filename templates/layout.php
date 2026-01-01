<?php
/**
 * Fő layout template
 * 
 * Elérhető változók:
 * - $content: string - A beágyazott tartalom
 * - $kategoriak: array - Kategóriák a menühöz
 * - $felhasznalo: ?FelhasznaloEntity - Bejelentkezett felhasználó
 * - $session: Session - Session objektum
 * - $csrf_token: string - CSRF token
 * - $flash: array - Flash üzenetek
 */
// Az e() függvény a helpers.php-ból elérhető
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e($csrf_token) ?>">
    <title>TudásBázis - Kérdezz, válaszolj, segíts!</title>
    <meta name="description" content="Kérdés-válasz közösségi oldal. Kérdezz bármit és kapj választ a közösségtől!">
    <link rel="stylesheet" href="/css/stilus.css">
</head>
<body>
    <!-- HEADER -->
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <div class="logo-icon">?</div>
                <a href="/" class="logo-text">TudásBázis</a>
            </div>
            
            <nav class="header-nav">
                <a href="/belepes"><span class="icon">👤</span> Belépés</a>
                <a href="/uj-kerdes"><span class="icon">❓</span> Új kérdés</a>
                <a href="/kerdesek?szuro=megvalaszolatlan"><span class="icon">🎲</span> Véletlen</a>
            </nav>

            <form class="search-form" action="/kereses" method="get">
                <input type="text" name="q" class="search-input" placeholder="Keresés...">
                <button type="submit" class="search-btn">🔍</button>
            </form>
        </div>
    </header>

    <!-- MAIN CONTAINER -->
    <div class="main-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <?php if ($felhasznalo): ?>
            <!-- Felhasználó menü (bejelentkezve) -->
            <div class="user-menu">
                <div class="user-menu-header">
                    <?= e($felhasznalo->felhasznalonev) ?>
                </div>
                <ul class="user-menu-lista">
                    <li class="user-menu-item"><a href="/profil" class="user-menu-link">Profilom</a></li>
                    <li class="user-menu-item">
                        <a href="#" class="user-menu-link">Kérdéseim</a>
                        <ul style="list-style:none; padding-left: 15px;">
                            <li><a href="/profil" class="user-menu-link" style="font-size:11px;">Saját kérdéseid</a></li>
                            <li><a href="/profil" class="user-menu-link" style="font-size:11px;">Megválaszoltjaid</a></li>
                        </ul>
                    </li>
                    <li class="user-menu-item"><a href="/kilepes" class="user-menu-link">Kilépés</a></li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Kategória menü -->
            <nav class="kategoria-menu">
                <ul class="kategoria-lista">
                    <?php if (!$felhasznalo): ?>
                    <li class="kategoria-item">
                        <a href="/" class="kategoria-link">Kezdőoldal</a>
                    </li>
                    <?php endif; ?>
                    <?php foreach ($kategoriak ?? [] as $kategoria): ?>
                    <li class="kategoria-item">
                        <a href="/kategoria/<?= e($kategoria->slug) ?>" class="kategoria-link">
                            <?= e($kategoria->nev) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <?php if (!empty($flash['success'])): ?>
            <div class="flash-message flash-success"><?= e($flash['success']) ?></div>
            <?php endif; ?>

            <?php if (!empty($flash['error'])): ?>
            <div class="flash-message flash-error"><?= e($flash['error']) ?></div>
            <?php endif; ?>

            <?php if (!empty($flash['warning'])): ?>
            <div class="flash-message flash-warning"><?= e($flash['warning']) ?></div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>

    <!-- FOOTER -->
    <footer class="site-footer">
        <p>&copy; <?= date('Y') ?> TudásBázis - Gyakori kérdések clone</p>
        <p><a href="/">Főoldal</a> | <a href="/belepes">Belépés</a> | <a href="/regisztracio">Regisztráció</a></p>
    </footer>

    <script src="/js/app.js"></script>
</body>
</html>
