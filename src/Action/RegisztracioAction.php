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
 * Regisztráció Action
 */
class RegisztracioAction
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
        $adatok = [];

        if ($request->isPost()) {
            // CSRF ellenőrzés
            $csrfToken = $request->post('csrf_token', '');
            if (!$this->session->validateCsrfToken($csrfToken)) {
                $hiba = 'Érvénytelen munkamenet. Kérlek próbáld újra.';
            } else {
                $adatok = [
                    'felhasznalonev' => trim($request->post('felhasznalonev', '')),
                    'email' => trim($request->post('email', '')),
                    'jelszo' => $request->post('jelszo', ''),
                    'jelszo_megerosites' => $request->post('jelszo_megerosites', ''),
                    'nev_latszik' => $request->post('nev_latszik', 'nem') === 'igen',
                    'szabalyzat' => $request->post('szabalyzat') === '1',
                    'eletkor' => $request->post('eletkor') === '1',
                ];

                // Elfogadás ellenőrzése
                if (!$adatok['szabalyzat']) {
                    $hiba = 'El kell fogadnod a felhasználói szabályzatot.';
                } elseif (!$adatok['eletkor']) {
                    $hiba = 'Nyilatkoznod kell, hogy elmúltál 16 éves.';
                } else {
                    try {
                        $felhasznalo = $this->authService->regisztral(
                            $adatok['felhasznalonev'],
                            $adatok['email'],
                            $adatok['jelszo'],
                            $adatok['jelszo_megerosites'],
                            $adatok['nev_latszik']
                        );

                        // Automatikus bejelentkezés
                        $this->authService->bejelentkezik($adatok['email'], $adatok['jelszo']);
                        $this->session->flash('success', 'Sikeres regisztráció! Üdvözölünk a TudásBázison!');
                        return (new Response())->redirect('/');
                    } catch (\InvalidArgumentException | \RuntimeException $e) {
                        $hiba = $e->getMessage();
                    }
                }
            }
        }

        $kategoriak = $this->kategoriaService->getFokategoriakAlkategoriakkal();

        $html = $this->view->renderWithLayout('belepes', [
            'hiba' => $hiba,
            'adatok' => $adatok,
            'kategoriak' => $kategoriak,
            'felhasznalo' => null,
            'regisztracio' => true
        ]);

        return Response::html($html);
    }
}
