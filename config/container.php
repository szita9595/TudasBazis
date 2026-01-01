<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use App\Core\Database;
use App\Core\Session;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\App;
use App\View\ViewRenderer;

// Domain Services
use App\Domain\Felhasznalo\FelhasznaloRepository;
use App\Domain\Felhasznalo\AuthService;
use App\Domain\Felhasznalo\RememberMeService;
use App\Domain\Kategoria\KategoriaRepository;
use App\Domain\Kategoria\KategoriaService;
use App\Domain\Kerdes\KerdesRepository;
use App\Domain\Kerdes\KerdesService;
use App\Domain\Valasz\ValaszRepository;
use App\Domain\Valasz\ValaszService;
use App\Domain\Szavazas\SzavazasService;

// Actions
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

$containerBuilder = new ContainerBuilder();

$containerBuilder->addDefinitions([
    // Core - Database
    Database::class => function () {
        return new Database(
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_DATABASE'] ?? 'tudasbazis',
            $_ENV['DB_USERNAME'] ?? 'root',
            $_ENV['DB_PASSWORD'] ?? '',
            $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            (int) ($_ENV['DB_PORT'] ?? 3306)
        );
    },

    // Core - Session
    Session::class => function () {
        return new Session();
    },

    // Core - Request
    Request::class => function () {
        return new Request();
    },

    // View Renderer
    ViewRenderer::class => function (Session $session) {
        return new ViewRenderer($session);
    },

    // Router
    Router::class => function (\Psr\Container\ContainerInterface $c) {
        return new Router($c);
    },

    // App
    App::class => function (\Psr\Container\ContainerInterface $c) {
        return new App($c);
    },

    // ========== Repositories ==========
    FelhasznaloRepository::class => function (Database $db) {
        return new FelhasznaloRepository($db);
    },

    KategoriaRepository::class => function (Database $db) {
        return new KategoriaRepository($db);
    },

    KerdesRepository::class => function (Database $db) {
        return new KerdesRepository($db);
    },

    ValaszRepository::class => function (Database $db) {
        return new ValaszRepository($db);
    },

    // ========== Services ==========
    RememberMeService::class => function (Database $db) {
        return new RememberMeService($db);
    },

    AuthService::class => function (FelhasznaloRepository $repo, Session $session, RememberMeService $rememberMe) {
        $auth = new AuthService($repo, $session);
        $auth->setRememberMeService($rememberMe);
        return $auth;
    },

    KategoriaService::class => function (KategoriaRepository $repo) {
        return new KategoriaService($repo);
    },

    KerdesService::class => function (KerdesRepository $repo) {
        return new KerdesService($repo);
    },

    ValaszService::class => function (ValaszRepository $repo) {
        return new ValaszService($repo);
    },

    SzavazasService::class => function (Database $db, ValaszRepository $valaszRepo, FelhasznaloRepository $felhasznaloRepo) {
        return new SzavazasService($db, $valaszRepo, $felhasznaloRepo);
    },

    // ========== Actions ==========
    FooldalAction::class => function (ViewRenderer $view, KategoriaService $kategoriaService, KerdesService $kerdesService, AuthService $authService) {
        return new FooldalAction($view, $kategoriaService, $kerdesService, $authService);
    },

    BelepesAction::class => function (ViewRenderer $view, AuthService $authService, Session $session, KategoriaService $kategoriaService) {
        return new BelepesAction($view, $authService, $session, $kategoriaService);
    },

    RegisztracioAction::class => function (ViewRenderer $view, AuthService $authService, Session $session, KategoriaService $kategoriaService) {
        return new RegisztracioAction($view, $authService, $session, $kategoriaService);
    },

    KilepesAction::class => function (Session $session) {
        return new KilepesAction($session);
    },

    ProfilAction::class => function (ViewRenderer $view, AuthService $authService, KategoriaService $kategoriaService, KerdesService $kerdesService, ValaszService $valaszService) {
        return new ProfilAction($view, $authService, $kategoriaService, $kerdesService, $valaszService);
    },

    KategoriaAction::class => function (ViewRenderer $view, KategoriaService $kategoriaService, KerdesService $kerdesService, AuthService $authService) {
        return new KategoriaAction($view, $kategoriaService, $kerdesService, $authService);
    },

    KerdesListaAction::class => function (ViewRenderer $view, KategoriaService $kategoriaService, KerdesService $kerdesService, AuthService $authService) {
        return new KerdesListaAction($view, $kategoriaService, $kerdesService, $authService);
    },

    KerdesReszletekAction::class => function (ViewRenderer $view, KategoriaService $kategoriaService, KerdesService $kerdesService, ValaszService $valaszService, AuthService $authService) {
        return new KerdesReszletekAction($view, $kategoriaService, $kerdesService, $valaszService, $authService);
    },

    UjKerdesAction::class => function (ViewRenderer $view, KerdesService $kerdesService, KategoriaService $kategoriaService, AuthService $authService, Session $session) {
        return new UjKerdesAction($view, $kerdesService, $kategoriaService, $authService, $session);
    },

    ValaszAction::class => function (ValaszService $valaszService, AuthService $authService, Session $session) {
        return new ValaszAction($valaszService, $authService, $session);
    },

    SzavazasAction::class => function (SzavazasService $szavazasService, AuthService $authService, Session $session) {
        return new SzavazasAction($szavazasService, $authService, $session);
    },

    KeresesAction::class => function (ViewRenderer $view, KerdesService $kerdesService, KategoriaService $kategoriaService, AuthService $authService) {
        return new KeresesAction($view, $kerdesService, $kategoriaService, $authService);
    },
]);

return $containerBuilder->build();
