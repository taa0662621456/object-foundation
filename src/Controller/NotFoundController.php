<?php
declare(strict_types=1);

namespace ObjectFoundation\Controller;

use ObjectFoundation\Http\{Request, Response};

final class NotFoundController
{
    public function handle(Request $req): Response
    {
        return Response::json(['error' => 'Not found'], 404);
    }
}
