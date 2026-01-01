<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\View\ViewRenderer;
use App\Domain\Kategoria\KategoriaService;
use App\Domain\Kerdes\KerdesService;
use App\Domain\Felhasznalo\AuthService;

/**
 * Kérdések lista Action
 */
class KerdesListaAction
{
    public function __construct(
        private ViewRenderer $view,
        private KategoriaService $kategoriaService,
        private KerdesService $kerdesService,
        private AuthService $authService
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        $szuro = $request->get('szuro', 'legujabb');
        $oldal = max(1, (int) $request->get('oldal', 1));

        // Szűrés típusa alapján
        $kerdesek = match ($szuro) {
            'megvalaszolatlan' => $this->kerdesService->getMegvalaszolatlan(20, $oldal),
            default => $this->kerdesService->getLegujabbak(20, $oldal)
        };

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();
        $felhasznalo = $this->authService->aktualisFelhasznalo();

        $html = $this->view->renderWithLayout('kerdes/lista', [
            'kerdesek' => $kerdesek,
            'szuro' => $szuro,
            'oldal' => $oldal,
            'kategoriak' => $kategoriak,
            'felhasznalo' => $felhasznalo
        ]);

        return Response::html($html);
    }
}
