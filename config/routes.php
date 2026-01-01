<?php

declare(strict_types=1);

/**
 * Route definíciók
 * 
 * A $router változó elérhető ebben a scope-ban
 */

use App\Action\FooldalAction;
use App\Action\BelepesAction;
use App\Action\RegisztracioAction;
use App\Action\KilepesAction;
use App\Action\ProfilAction;
use App\Action\KategoriaAction;
use App\Action\KerdesListaAction;
use App\Action\KerdesReszletekAction;
use App\Action\UjKerdesAction;
use App\Action\ValaszAction;
use App\Action\SzavazasAction;
use App\Action\KeresesAction;

// Főoldal
$router->get('/', FooldalAction::class);

// Autentikáció
$router->any('/belepes', BelepesAction::class);
$router->any('/regisztracio', RegisztracioAction::class);
$router->get('/kilepes', KilepesAction::class);

// Profil
$router->get('/profil', ProfilAction::class);
$router->get('/profil/{id}', ProfilAction::class);

// Kategóriák
$router->get('/kategoria/{slug}', KategoriaAction::class);
$router->get('/kategoria/{slug}/{alkategoria}', KategoriaAction::class);

// Kérdések
$router->get('/kerdesek', KerdesListaAction::class);
$router->get('/kerdes/{id}', KerdesReszletekAction::class);
$router->get('/kerdes/{id}/{slug}', KerdesReszletekAction::class);
$router->any('/uj-kerdes', UjKerdesAction::class);

// Válaszok
$router->post('/valasz', ValaszAction::class);
$router->post('/valasz/{kerdes_id}', ValaszAction::class);

// Szavazás
$router->post('/szavazas', SzavazasAction::class);
$router->post('/szavazas/{valasz_id}/{irany}', SzavazasAction::class);

// Keresés
$router->get('/kereses', KeresesAction::class);
