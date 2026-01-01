<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Valasz\ValaszService;
use App\Domain\Kerdes\KerdesRepository;
use App\Domain\Felhasznalo\AuthService;

/**
 * Válasz Action (POST)
 */
class ValaszAction
{
    public function __construct(
        private ValaszService $valaszService,
        private AuthService $authService,
        private Session $session
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        // Be kell jelentkezve lenni
        $felhasznalo = $this->authService->aktualisFelhasznalo();
        if ($felhasznalo === null) {
            $this->session->flash('error', 'Válaszadáshoz jelentkezz be!');
            return (new Response())->redirect('/belepes');
        }

        if (!$request->isPost()) {
            return (new Response())->redirect('/');
        }

        // CSRF ellenőrzés
        $csrfToken = $request->post('csrf_token', '');
        if (!$this->session->validateCsrfToken($csrfToken)) {
            $this->session->flash('error', 'Érvénytelen munkamenet.');
            return (new Response())->redirect('/');
        }

        $kerdesId = (int) ($params['kerdes_id'] ?? $request->post('kerdes_id', 0));
        $tartalom = trim($request->post('tartalom', ''));

        if ($kerdesId === 0) {
            $this->session->flash('error', 'Érvénytelen kérdés.');
            return (new Response())->redirect('/');
        }

        try {
            $this->valaszService->letrehoz($kerdesId, $felhasznalo->id, $tartalom);
            $this->session->flash('success', 'Válaszod sikeresen elküldve!');
        } catch (\InvalidArgumentException $e) {
            $this->session->flash('error', $e->getMessage());
        }

        return (new Response())->redirect('/kerdes/' . $kerdesId);
    }
}
