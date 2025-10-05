<?php
declare(strict_types=1);

namespace ObjectFoundation\Http\Middleware;

use ObjectFoundation\Http\{Request, Response};
// NOTE: Replace with your actual Auth implementation
use ObjectFoundation\Api\Security\Auth;

final class AuthMiddleware
{
    /** @var string[] */
    private array $publicPaths;

    public function __construct(array $publicPaths = [])
    {
        $this->publicPaths = $publicPaths;
    }

    public function process(Request $req, callable $next): Response
    {
        $requireAuth = filter_var((string) getenv('OBJECT_FOUNDATION_REQUIRE_AUTH'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        $requireAuth = $requireAuth ?? true; // default true

        if ($requireAuth && !in_array($req->path, $this->publicPaths, true)) {
            if (!class_exists(Auth::class) || !Auth::isAuthorized($_SERVER)) {
                return Response::json(['error' => 'Unauthorized'], 401);
            }
        }
        return $next($req);
    }
}
