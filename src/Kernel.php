<?php
declare(strict_types=1);

namespace ObjectFoundation;

use ObjectFoundation\Http\{Request, Response, Router};
use ObjectFoundation\Http\Middleware\{
    CorsMiddleware,
    AuthMiddleware,
    RateLimiterMiddleware,
    ObservabilityMiddleware
};

final class Kernel
{
    public function handle(Request $req): Response
    {
        $middlewares = [
            new CorsMiddleware(),
            new AuthMiddleware(['/api/openapi.json','/api/docs']),
            new RateLimiterMiddleware(),
            new ObservabilityMiddleware(),
        ];

        $router = new Router();
        $handler = fn(Request $r): Response => $router->dispatch($r);

        foreach (array_reverse($middlewares) as $m) {
            $next = $handler;
            $handler = fn(Request $r): Response => $m->process($r, $next);
        }

        return $handler($req);
    }
}
