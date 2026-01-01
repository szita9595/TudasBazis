<?php

declare(strict_types=1);

namespace App\Core;

use Psr\Container\ContainerInterface;

/**
 * URL Router - URL-eket Controller action-ökhöz rendel
 */
class Router
{
    private array $routes = [];
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * GET route hozzáadása
     */
    public function get(string $pattern, string $handler): self
    {
        return $this->addRoute('GET', $pattern, $handler);
    }

    /**
     * POST route hozzáadása
     */
    public function post(string $pattern, string $handler): self
    {
        return $this->addRoute('POST', $pattern, $handler);
    }

    /**
     * GET és POST route hozzáadása
     */
    public function any(string $pattern, string $handler): self
    {
        $this->addRoute('GET', $pattern, $handler);
        $this->addRoute('POST', $pattern, $handler);
        return $this;
    }

    /**
     * Route hozzáadása
     */
    private function addRoute(string $method, string $pattern, string $handler): self
    {
        // Pattern átalakítása regex-re
        $regex = $this->patternToRegex($pattern);
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'regex' => $regex,
            'handler' => $handler
        ];
        
        return $this;
    }

    /**
     * URL pattern regex-re alakítása
     * Pl.: /kerdes/{id}-{slug} -> /kerdes/(?P<id>[^/]+)-(?P<slug>[^/]+)
     */
    private function patternToRegex(string $pattern): string
    {
        // {param} -> (?P<param>[^/]+)
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        
        // Escape forward slashes
        $regex = str_replace('/', '\/', $regex);
        
        return '/^' . $regex . '$/';
    }

    /**
     * Request dispatch - megkeresi a megfelelő handler-t és futtatja
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $uri, $matches)) {
                // Csak a named captures-t tartjuk meg
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                return $this->callHandler($route['handler'], $request, $params);
            }
        }

        // Nincs találat - 404
        return Response::notFound();
    }

    /**
     * Handler meghívása a containerből
     */
    private function callHandler(string $handler, Request $request, array $params): Response
    {
        // Handler: App\Action\FooldalAction
        $action = $this->container->get($handler);
        
        // __invoke meghívása a request-tel és paraméterekkel
        return $action($request, $params);
    }

    /**
     * Routes betöltése fájlból
     */
    public function loadRoutes(string $routesFile): void
    {
        $router = $this;
        require $routesFile;
    }
}
