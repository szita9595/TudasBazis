<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\View\ViewRenderer;
use App\Domain\Felhasznalo\AuthService;
use App\Domain\Kategoria\KategoriaService;
use App\Domain\Kerdes\KerdesService;
use App\Domain\Valasz\ValaszService;

/**
 * Profil Action
 */
class ProfilAction
{
    public function __construct(
        private ViewRenderer $view,
        private AuthService $authService,
        private KategoriaService $kategoriaService,
        private KerdesService $kerdesService,
        private ValaszService $valaszService
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        $felhasznaloId = isset($params['id']) ? (int) $params['id'] : null;
        
        // Ha nincs ID megadva, a bejelentkezett felhasználó profilját mutatjuk
        if ($felhasznaloId === null) {
            $aktualis = $this->authService->aktualisFelhasznalo();
            if ($aktualis === null) {
                return (new Response())->redirect('/belepes');
            }
            $felhasznaloId = $aktualis->id;
        }

        $profilFelhasznalo = $this->authService->findById($felhasznaloId);
        
        if ($profilFelhasznalo === null) {
            return Response::notFound('A felhasználó nem található.');
        }

        // Felhasználó statisztikái
        $kerdesek = $this->kerdesService->getByFelhasznalo($felhasznaloId, 10);
        $valaszok = $this->valaszService->getByFelhasznalo($felhasznaloId, 10);
        $valaszokSzama = $this->valaszService->countByFelhasznalo($felhasznaloId);

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();
        $bejelentkezett = $this->authService->aktualisFelhasznalo();

        $html = $this->view->renderWithLayout('profil', [
            'profilFelhasznalo' => $profilFelhasznalo,
            'kerdesek' => $kerdesek,
            'valaszok' => $valaszok,
            'valaszokSzama' => $valaszokSzama,
            'kategoriak' => $kategoriak,
            'felhasznalo' => $bejelentkezett
        ]);

        return Response::html($html);
    }
}
