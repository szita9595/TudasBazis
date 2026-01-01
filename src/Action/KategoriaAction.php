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
 * Kategória Action
 */
class KategoriaAction
{
    public function __construct(
        private ViewRenderer $view,
        private KategoriaService $kategoriaService,
        private KerdesService $kerdesService,
        private AuthService $authService
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        $slug = $params['slug'] ?? '';
        $alkategoriaSlug = $params['alkategoria'] ?? null;

        $kategoria = $this->kategoriaService->getBySlug($slug);
        
        if ($kategoria === null) {
            return Response::notFound('A kategória nem található.');
        }

        // Ha van alkategória
        $aktualisKategoria = $kategoria;
        if ($alkategoriaSlug !== null) {
            $alkategoria = $this->kategoriaService->getBySlug($alkategoriaSlug);
            if ($alkategoria !== null) {
                $aktualisKategoria = $alkategoria;
            }
        }

        // Oldalszám
        $oldal = max(1, (int) $request->get('oldal', 1));

        // Kérdések lekérdezése
        $kerdesek = $this->kerdesService->getByKategoria($aktualisKategoria->id, 20, $oldal);
        $osszesKerdes = $this->kerdesService->countByKategoria($aktualisKategoria->id);

        // Alkategóriák (ha főkategória)
        $alkategoriak = $this->kategoriaService->getAlkategoriak($kategoria->id);

        // Breadcrumb
        $breadcrumb = $this->kategoriaService->getBreadcrumb($aktualisKategoria->id);

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();
        $felhasznalo = $this->authService->aktualisFelhasznalo();

        $html = $this->view->renderWithLayout('kategoria/lista', [
            'kategoria' => $kategoria,
            'aktualisKategoria' => $aktualisKategoria,
            'alkategoriak' => $alkategoriak,
            'kerdesek' => $kerdesek,
            'osszesKerdes' => $osszesKerdes,
            'oldal' => $oldal,
            'breadcrumb' => $breadcrumb,
            'kategoriak' => $kategoriak,
            'felhasznalo' => $felhasznalo
        ]);

        return Response::html($html);
    }
}
