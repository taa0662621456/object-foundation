<?php
declare(strict_types=1);

namespace ObjectFoundation\Http\Middleware;

use ObjectFoundation\Http\{Request, Response};
// NOTE: Replace with your actual RateLimiter implementation
use ObjectFoundation\Api\Security\RateLimiter;

final class RateLimiterMiddleware
{
    public function process(Request $req, callable $next): Response
    {
        $storage = getenv('OBJECT_FOUNDATION_RL_STORAGE') ?: __DIR__ . '/../../../var/runtime/ratelimit.json';
        $limit   = (int) (getenv('OBJECT_FOUNDATION_RL_LIMIT') ?: 60);
        $window  = (int) (getenv('OBJECT_FOUNDATION_RL_WINDOW') ?: 60);

        if (!class_exists(RateLimiter::class)) {
            return $next($req); // soft-fallback if class not present
        }

        $limiter = new RateLimiter($storage, $limit, $window);
        $clientKey = $req->ip ?: 'unknown';

        if (!$limiter->allow($clientKey)) {
            return Response::json(['error' => 'Too Many Requests'], 429, $limiter->headers($clientKey));
        }

        $resp = $next($req);
        return new Response($resp->status, array_merge($resp->headers, $limiter->headers($clientKey)), $resp->body);
    }
}
