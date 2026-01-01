<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\View\ViewRenderer;
use App\Domain\Kerdes\KerdesService;
use App\Domain\Kategoria\KategoriaService;
use App\Domain\Felhasznalo\AuthService;

/**
 * Új kérdés Action
 */
class UjKerdesAction
{
    public function __construct(
        private ViewRenderer $view,
        private KerdesService $kerdesService,
        private KategoriaService $kategoriaService,
        private AuthService $authService,
        private Session $session
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        // Be kell jelentkezve lenni
        $felhasznalo = $this->authService->aktualisFelhasznalo();
        if ($felhasznalo === null) {
            $this->session->flash('error', 'Kérdés feltevéséhez jelentkezz be!');
            return (new Response())->redirect('/belepes');
        }

        $hiba = null;
        $adatok = [
            'cim' => '',
            'tartalom' => '',
            'kategoria_id' => null
        ];

        if ($request->isPost()) {
            // CSRF ellenőrzés
            $csrfToken = $request->post('csrf_token', '');
            if (!$this->session->validateCsrfToken($csrfToken)) {
                $hiba = 'Érvénytelen munkamenet. Kérlek próbáld újra.';
            } else {
                $adatok = [
                    'cim' => trim($request->post('cim', '')),
                    'tartalom' => trim($request->post('tartalom', '')),
                    'kategoria_id' => (int) $request->post('kategoria_id', 0)
                ];

                // Validálás
                if ($adatok['kategoria_id'] === 0) {
                    $hiba = 'Válassz kategóriát!';
                } else {
                    try {
                        $kerdes = $this->kerdesService->letrehoz(
                            $felhasznalo->id,
                            $adatok['kategoria_id'],
                            $adatok['cim'],
                            $adatok['tartalom']
                        );

                        $this->session->flash('success', 'A kérdésed sikeresen feltéve!');
                        return (new Response())->redirect('/kerdes/' . $kerdes->id . '/' . $kerdes->slug);
                    } catch (\InvalidArgumentException $e) {
                        $hiba = $e->getMessage();
                    }
                }
            }
        }

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();
        $osszesKategoria = $this->kategoriaService->getAll();

        $html = $this->view->renderWithLayout('kerdes/uj', [
            'hiba' => $hiba,
            'adatok' => $adatok,
            'kategoriak' => $kategoriak,
            'osszesKategoria' => $osszesKategoria,
            'felhasznalo' => $felhasznalo
        ]);

        return Response::html($html);
    }
}
