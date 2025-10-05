<?php
declare(strict_types=1);

namespace ObjectFoundation\Http\Middleware;

use ObjectFoundation\Http\{Request, Response};
use ObjectFoundation\Logger\MonologFactory;
// Optional user classes (if present)
use ObjectFoundation\Api\Observability\{MetricsCollector, RequestLogger};

final class ObservabilityMiddleware
{
    public function process(Request $req, callable $next): Response
    {
        $logger = MonologFactory::build();
        $resp = null;
        $status = 500;
        $cacheHit = false;

        try {
            $resp = $next($req);
            $status = $resp->status;
            $cacheHit = array_key_exists('ETag', $resp->headers);
            return $resp;
        } catch (\Throwable $e) {
            $logger->error('Unhandled exception', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return Response::json(['error' => 'Internal Server Error'], 500);
        } finally {
            $duration = round((microtime(true) - $req->startedAt) * 1000.0, 2);
            $ua = $req->header('User-Agent', '');
            $authHeader = $req->header('Authorization', '');
            $authType = str_starts_with($authHeader, 'ApiKey ') ? 'ApiKey' : (str_starts_with($authHeader, 'Bearer ') ? 'Bearer' : '');

            // Log basic access
            $logger->info('access', [
                'ip' => $req->ip,
                'method' => $req->method,
                'path' => $req->path,
                'status' => $status,
                'duration_ms' => $duration,
                'user_agent' => $ua,
                'auth' => $authType,
                'cache_hit' => $cacheHit,
            ]);

            if (class_exists(MetricsCollector::class)) {
                (new MetricsCollector())->addRequest($duration, $status, $cacheHit, $status === 401);
            }
            if (class_exists(RequestLogger::class)) {
                (new RequestLogger())->log([
                    'ip' => $req->ip,
                    'method' => $req->method,
                    'path' => $req->path,
                    'status' => $status,
                    'duration_ms' => $duration,
                    'user_agent' => $ua,
                    'auth' => $authType,
                    'cache_hit' => $cacheHit,
                ]);
            }
        }
    }
}
