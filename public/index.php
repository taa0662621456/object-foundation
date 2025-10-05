<?php
require __DIR__ . '/../vendor/autoload.php';

use JetBrains\PhpStorm\NoReturn;
use ObjectFoundation\Ontology\Oql\{Parser, Executor};
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Ontology\Exporter\JsonLdExporter;
use ObjectFoundation\Cache\ManifestCache;
use ObjectFoundation\Api\Observability\MetricsCollector;
use ObjectFoundation\Events\OutboxStorage;
use ObjectFoundation\Api\Security\Auth;
use ObjectFoundation\Api\Security\RateLimiter;
use ObjectFoundation\Api\OpenApiGenerator;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$__of_t0 = microtime(true);
$method = $_SERVER['REQUEST_METHOD'];

// --- CORS ---
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allowedOrigins = getenv('CORS_ORIGINS') ?: '*';
$allow = ($allowedOrigins === '*') ? '*' : (in_array($origin, array_map('trim', explode(',', $allowedOrigins)), true) ? $origin : 'null');
header('Access-Control-Allow-Origin: ' . $allow);
header('Vary: Origin');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Expose-Headers: X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); exit;
}

// --- Auth ---
$publicPaths = ['/api/openapi.json', '/api/docs'];
$requireAuth = getenv('OBJECT_FOUNDATION_REQUIRE_AUTH');
$requireAuth = $requireAuth === false || strtolower($requireAuth) !== 'false' && $requireAuth !== '0';

if ($requireAuth && !in_array($path, $publicPaths, true)) {
    if (!Auth::isAuthorized($_SERVER)) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_SLASHES);
        exit;
    }
}

// --- Rate limit (per IP) ---
$limiter = new RateLimiter(getenv('OBJECT_FOUNDATION_RL_STORAGE') ?: __DIR__ . '/../var/runtime/ratelimit.json',
                           (int)(getenv('OBJECT_FOUNDATION_RL_LIMIT') ?: 60),
                           (int)(getenv('OBJECT_FOUNDATION_RL_WINDOW') ?: 60));
$clientKey = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!$limiter->allow($clientKey)) {
    foreach ($limiter->headers($clientKey) as $k=>$v) header($k.': '.$v);
    http_response_code(429);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error'=>'Too Many Requests'], JSON_UNESCAPED_SLASHES);
    exit;
}
foreach ($limiter->headers($clientKey) as $k=>$v) header($k.': '.$v);


function read_json() {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}


function etag_headers(string $payload, int $ts = null): void {
    $etag = '"' . sha1($payload) . '"';
    header('ETag: ' . $etag);
    if ($ts) header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $ts) . ' GMT');
    $inm = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
    $ims = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
    if ($inm === $etag || ($ims && $ts && strtotime($ims) >= $ts)) {
        http_response_code(304);
        exit;
    }
}

#[NoReturn] function respond_json($data, int $code = 200, string $contentType = 'application/json') {
    http_response_code($code);
    header('Content-Type: ' . $contentType . '; charset=utf-8');
    echo is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

try {
    if ($path === '/api/metrics') {
        $mc = new MetricsCollector();
        $snap = $mc->snapshot();
        $export = getenv('OBJECT_FOUNDATION_METRICS_EXPORT');
        $export = $export === false || strtolower($export) !== 'false' && $export !== '0';
        if ($export && ($_GET['format'] ?? '') === 'prometheus') {
            header('Content-Type: text/plain; charset=utf-8');
            echo $mc->toPrometheus($snap);
            exit;
        }
        respond_json($snap);
    }

    if ($path === '/api/openapi.json') {
        $gen = new OpenApiGenerator();
        $json = $gen->toJson($gen->build());
        respond_json($json);
    }

    if ($path === '/api/docs') {
        $html = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Object Foundation API — Swagger UI</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script>
    window.onload = () => {
      window.ui = SwaggerUIBundle({
        url: '/api/openapi.json',
        dom_id: '#swagger-ui',
      });
    };
  </script>
</body>
</html>
HTML;
        header('Content-Type: text/html; charset=utf-8');
        echo $html; exit;
    }

    if ($path === '/api/oql') {
        $q = $_GET['query'] ?? null;
        $classes = isset($_GET['classes']) ? (array)$_GET['classes'] : [];
        if ($method === 'POST') {
            $body = read_json();
            $q = $body['query'] ?? $q;
            $classes = $body['classes'] ?? $classes;
        }
        if (!$q) respond_json(['error'=>'Missing query'], 400);

        $parser = new Parser();
        $query = $parser->parse($q);
        $exec = new Executor();
        $cache = new ManifestCache();
        $rows = $cache->result('oql:'.sha1($q.'|'.json_encode($classes)), function() use($exec,$query,$classes){ return $exec->run($query, $classes); });

        $format = $_GET['format'] ?? ($body['format'] ?? 'json');
        if ($format === 'jsonld') {
            $collector = new ManifestCollector();
            $manifests = [];
            foreach ($rows as $r) {
                if (!empty($r['entity']) && class_exists($r['entity'])) {
                    $manifests[] = (new ManifestCache())->manifestFor($r['entity'], $collector);
                }
            }
            $jsonld = (new JsonLdExporter())->export($manifests);
            etag_headers($jsonld, time());
            respond_json($jsonld, 200, 'application/ld+json');
        }
        $payload = json_encode($rows, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        etag_headers($payload, time());
        // OUTBOX EVENT
        if ($method === 'POST') {
            (new OutboxStorage())->append('OQLQueryExecuted', [
                'query' => $q,
                'classes' => $classes,
                'rows' => count($rows)
            ]);
        }
        respond_json($payload);
    }

    if ($path === '/api/ontology/entities') {
        $classes = isset($_GET['classes']) ? (array)$_GET['classes'] : [];
        $collector = new ManifestCollector();
        $out = [];
        $cache = new ManifestCache();
        foreach ($classes as $c) {
            if (class_exists($c)) $out[] = $cache->manifestFor($c, $collector);
        }
        $payload = json_encode($out, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        etag_headers($payload, time());
        // OUTBOX EVENT
        if ($method === 'POST') {
            (new OutboxStorage())->append('OQLQueryExecuted', [
                'query' => $q,
                'classes' => $classes,
                'rows' => count($rows)
            ]);
        }
        respond_json($payload);
    }

    if ($path === '/api/ontology/entity') {
        $name = $_GET['name'] ?? null;
        if (!$name || !class_exists($name)) respond_json(['error'=>'class not found'], 404);
        $collector = new ManifestCollector();
        $cache = new ManifestCache();
        $manifest = $cache->manifestFor($name, $collector);
        $payload = json_encode($manifest, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        etag_headers($payload, time());
        // OUTBOX EVENT
        if ($method === 'POST') {
            (new OutboxStorage())->append('OQLQueryExecuted', [
                'query' => $q,
                'classes' => $classes,
                'rows' => count($rows)
            ]);
        }
        respond_json($payload);
    }

    if ($path === '/api/ontology/traits') {
        $traits = array_filter(get_declared_traits(), fn($t) => str_starts_with($t, 'ObjectFoundation\\Traits\\'));
        respond_json(array_values($traits));
    }

    respond_json(['error'=>'Not found'], 404);
} catch (Throwable $e) {
    respond_json(['error'=>$e->getMessage()], 500);
}

// ---- Observability: finalize ----
register_shutdown_function(function() use($__of_t0, $path) {
    $duration = (microtime(true) - $__of_t0) * 1000.0;
    $status = http_response_code() ?: 200;
    $logger = new \ObjectFoundation\Api\Observability\RequestLogger();
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['Authorization'] ?? '');
    $authType = str_starts_with($authHeader, 'ApiKey ') ? 'ApiKey' : (str_starts_with($authHeader, 'Bearer ') ? 'Bearer' : '');
    $cacheHit = headers_list();
    $cacheHitFlag = false;
    foreach ($cacheHit as $h) { if (stripos($h, 'ETag:') === 0) { $cacheHitFlag = true; break; } }
    $logger->log([
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'method' => $_SERVER['REQUEST_METHOD'] ?? '',
        'path' => $path,
        'status' => $status,
        'duration_ms' => round($duration, 2),
        'user_agent' => $ua,
        'auth' => $authType,
        'cache_hit' => $cacheHitFlag
    ]);
    (new MetricsCollector())->addRequest($duration, $status, $cacheHitFlag, $status===401);
});
