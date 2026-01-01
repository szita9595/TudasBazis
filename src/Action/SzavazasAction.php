<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Szavazas\SzavazasService;
use App\Domain\Felhasznalo\AuthService;

/**
 * Szavazás Action (POST)
 */
class SzavazasAction
{
    public function __construct(
        private SzavazasService $szavazasService,
        private AuthService $authService,
        private Session $session
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        // Be kell jelentkezve lenni
        $felhasznalo = $this->authService->aktualisFelhasznalo();
        if ($felhasznalo === null) {
            return (new Response())->json(['error' => 'Szavazáshoz jelentkezz be!'], 401);
        }

        if (!$request->isPost()) {
            return (new Response())->json(['error' => 'Csak POST kérés engedélyezett'], 405);
        }

        // CSRF ellenőrzés
        $csrfToken = $request->post('csrf_token', '');
        if (!$this->session->validateCsrfToken($csrfToken)) {
            return (new Response())->json(['error' => 'Érvénytelen munkamenet'], 403);
        }

        $valaszId = (int) ($params['valasz_id'] ?? $request->post('valasz_id', 0));
        $iranyStr = $params['irany'] ?? $request->post('irany', '');
        
        // Irány konvertálása
        $irany = match ($iranyStr) {
            'hasznos', '1', 1 => 1,
            'nem_hasznos', '-1', -1 => -1,
            default => 0
        };

        if ($valaszId === 0 || $irany === 0) {
            return (new Response())->json(['error' => 'Érvénytelen szavazat'], 400);
        }

        try {
            $this->szavazasService->szavaz($felhasznalo->id, $valaszId, $irany);
            return (new Response())->json(['success' => true, 'message' => 'Szavazat rögzítve!']);
        } catch (\RuntimeException $e) {
            return (new Response())->json(['error' => $e->getMessage()], 400);
        } catch (\InvalidArgumentException $e) {
            return (new Response())->json(['error' => $e->getMessage()], 400);
        }
    }
}
