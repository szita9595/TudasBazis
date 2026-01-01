<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\View\ViewRenderer;
use App\Domain\Felhasznalo\AuthService;
use App\Domain\Kategoria\KategoriaService;

/**
 * Bejelentkezés Action
 */
class BelepesAction
{
    public function __construct(
        private ViewRenderer $view,
        private AuthService $authService,
        private Session $session,
        private KategoriaService $kategoriaService
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        // Ha már be van jelentkezve, átirányítás
        if ($this->authService->bejelentkezveVan()) {
            return (new Response())->redirect('/');
        }

        $hiba = null;

        if ($request->isPost()) {
            // CSRF ellenőrzés
            $csrfToken = $request->post('csrf_token', '');
            if (!$this->session->validateCsrfToken($csrfToken)) {
                $hiba = 'Érvénytelen munkamenet. Kérlek próbáld újra.';
            } else {
                $email = trim($request->post('email', ''));
                $jelszo = $request->post('jelszo', '');

                try {
                    $this->authService->bejelentkezik($email, $jelszo);
                    $this->session->flash('success', 'Sikeres bejelentkezés!');
                    return (new Response())->redirect('/');
                } catch (\RuntimeException $e) {
                    $hiba = $e->getMessage();
                }
            }
        }

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();

        $html = $this->view->renderWithLayout('belepes', [
            'hiba' => $hiba,
            'kategoriak' => $kategoriak,
            'felhasznalo' => null
        ]);

        return Response::html($html);
    }
}
