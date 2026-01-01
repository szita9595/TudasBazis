<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\View\ViewRenderer;
use App\Domain\Kerdes\KerdesService;
use App\Domain\Kategoria\KategoriaService;
use App\Domain\Felhasznalo\AuthService;

/**
 * Keresés Action
 */
class KeresesAction
{
    public function __construct(
        private ViewRenderer $view,
        private KerdesService $kerdesService,
        private KategoriaService $kategoriaService,
        private AuthService $authService
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        $kereses = trim($request->get('q', ''));
        $talalatok = [];

        if (strlen($kereses) >= 3) {
            $talalatok = $this->kerdesService->keres($kereses);
        }

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();
        $felhasznalo = $this->authService->aktualisFelhasznalo();

        $html = $this->view->renderWithLayout('kereses', [
            'kereses' => $kereses,
            'talalatok' => $talalatok,
            'kategoriak' => $kategoriak,
            'felhasznalo' => $felhasznalo
        ]);

        return Response::html($html);
    }
}
