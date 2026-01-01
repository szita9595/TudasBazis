<?php

declare(strict_types=1);

namespace App\View;

use App\Core\Session;

// Helper függvények betöltése
require_once dirname(__DIR__) . '/helpers.php';

/**
 * View renderelő - PHP template-ek feldolgozása
 */
class ViewRenderer
{
    private string $templatesPath;
    private Session $session;
    private array $sharedData = [];

    public function __construct(Session $session)
    {
        $this->templatesPath = dirname(__DIR__, 2) . '/templates';
        $this->session = $session;
    }

    /**
     * Globális adat beállítása minden view-hoz
     */
    public function share(string $key, mixed $value): void
    {
        $this->sharedData[$key] = $value;
    }

    /**
     * Template renderelése
     */
    public function render(string $template, array $data = []): string
    {
        $templateFile = $this->templatesPath . '/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            throw new \RuntimeException("Template nem található: $template");
        }

        // Adatok összefűzése
        $data = array_merge($this->sharedData, $data, [
            'session' => $this->session,
            'csrf_token' => $this->session->getCsrfToken(),
            'flash' => [
                'success' => $this->session->getFlash('success'),
                'error' => $this->session->getFlash('error'),
                'warning' => $this->session->getFlash('warning'),
            ],
        ]);

        // Output buffering
        ob_start();
        extract($data);
        require $templateFile;
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Template renderelése layout-tal
     */
    public function renderWithLayout(string $template, array $data = [], string $layout = 'layout'): string
    {
        // Először a content template-et rendereljük
        $content = $this->render($template, $data);
        
        // Majd a layout-ot a content-tel
        return $this->render($layout, array_merge($data, ['content' => $content]));
    }

    /**
     * HTML escape helper
     */
    public static function escape(?string $value): string
    {
        return e($value ?? '');
    }
}

