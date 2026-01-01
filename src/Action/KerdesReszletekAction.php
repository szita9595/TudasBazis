<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\View\ViewRenderer;
use App\Domain\Kategoria\KategoriaService;
use App\Domain\Kerdes\KerdesService;
use App\Domain\Valasz\ValaszService;
use App\Domain\Felhasznalo\AuthService;

/**
 * Kérdés részletek Action
 */
class KerdesReszletekAction
{
    public function __construct(
        private ViewRenderer $view,
        private KategoriaService $kategoriaService,
        private KerdesService $kerdesService,
        private ValaszService $valaszService,
        private AuthService $authService
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        $kerdesId = isset($params['id']) ? (int) $params['id'] : 0;

        $kerdes = $this->kerdesService->getById($kerdesId, true);

        if ($kerdes === null) {
            return Response::notFound('A kérdés nem található.');
        }

        // Válaszok lekérdezése
        $valaszok = $this->valaszService->getByKerdes($kerdesId);

        // Kategória és breadcrumb
        $kategoria = $this->kategoriaService->getById($kerdes->kategoriaId);
        $breadcrumb = $this->kategoriaService->getBreadcrumb($kerdes->kategoriaId);

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();
        $felhasznalo = $this->authService->aktualisFelhasznalo();

        $html = $this->view->renderWithLayout('kerdes/reszletek', [
            'kerdes' => $kerdes,
            'valaszok' => $valaszok,
            'kategoria' => $kategoria,
            'breadcrumb' => $breadcrumb,
            'kategoriak' => $kategoriak,
            'felhasznalo' => $felhasznalo
        ]);

        return Response::html($html);
    }
}
