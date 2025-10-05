<?php
declare(strict_types=1);

namespace ObjectFoundation\Http\Middleware;

use ObjectFoundation\Http\{Request, Response};

final class CorsMiddleware
{
    public function process(Request $req, callable $next): Response
    {
        $origin = $req->header('Origin', '*');
        $allowed = getenv('CORS_ORIGINS') ?: '*';
        $allow = ($allowed === '*') ? '*' : (in_array($origin, array_map('trim', explode(',', (string)$allowed)), true) ? $origin : 'null');

        if ($req->method === 'OPTIONS') {
            return new Response(204, [
                'Access-Control-Allow-Origin' => $allow,
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
                'Access-Control-Expose-Headers' => 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset',
                'Vary' => 'Origin',
            ]);
        }

        $resp = $next($req);
        return new Response(
            $resp->status,
            array_merge($resp->headers, [
                'Access-Control-Allow-Origin' => $allow,
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type',
                'Access-Control-Expose-Headers' => 'X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset',
                'Vary' => 'Origin',
            ]),
            $resp->body
        );
    }
}
