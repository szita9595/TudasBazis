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
 * Főoldal Action
 */
class FooldalAction
{
    public function __construct(
        private ViewRenderer $view,
        private KategoriaService $kategoriaService,
        private KerdesService $kerdesService,
        private AuthService $authService
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        // Kategóriák lekérdezése a menühöz
        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();
        
        // Legújabb kérdések kategóriánként (mint a gyakorikerdesek.hu főoldalán)
        $kerdesekKategoriankent = [];
        foreach (array_slice($kategoriak, 0, 8) as $kategoria) {
            $kerdesekKategoriankent[$kategoria->id] = [
                'kategoria' => $kategoria,
                'kerdesek' => $this->kerdesService->getByKategoria($kategoria->id, 3)
            ];
        }

        // Statisztikák
        $statisztikak = [
            'osszes_kerdes' => $this->kerdesService->countAll(),
        ];

        // Bejelentkezett felhasználó
        $felhasznalo = $this->authService->aktualisFelhasznalo();

        $html = $this->view->renderWithLayout('fooldal', [
            'kategoriak' => $kategoriak,
            'kerdesekKategoriankent' => $kerdesekKategoriankent,
            'statisztikak' => $statisztikak,
            'felhasznalo' => $felhasznalo
        ]);

        return Response::html($html);
    }
}
