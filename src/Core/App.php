<?php

declare(strict_types=1);

namespace App\Core;

use Psr\Container\ContainerInterface;

/**
 * Alkalmazás bootstrap
 */
class App
{
    private ContainerInterface $container;
    private Router $router;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get(Router::class);
    }

    /**
     * Alkalmazás futtatása
     */
    public function run(): void
    {
        // Routes betöltése
        $routesFile = dirname(__DIR__, 2) . '/config/routes.php';
        $this->router->loadRoutes($routesFile);

        // Request létrehozása
        $request = $this->container->get(Request::class);

        // Dispatch és response küldése
        $response = $this->router->dispatch($request);
        $response->send();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
