<?php

declare(strict_types=1);

namespace App\Action;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Kijelentkezés Action
 */
class KilepesAction
{
    public function __construct(
        private Session $session
    ) {}

    public function __invoke(Request $request, array $params): Response
    {
        $this->session->destroy();
        
        return (new Response())->redirect('/');
    }
}
