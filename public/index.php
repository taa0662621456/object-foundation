<?php
require __DIR__ . '/../vendor/autoload.php';

use ObjectFoundation\Ontology\Oql\{Parser, Executor};
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Ontology\Exporter\JsonLdExporter;
use ObjectFoundation\Api\Security\Auth;
use ObjectFoundation\Api\Security\RateLimiter;
use ObjectFoundation\Api\OpenApiGenerator;

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

use ObjectFoundation\Api\Security\Auth;
use ObjectFoundation\Api\Security\RateLimiter;

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
$requireAuth = ($requireAuth === false) ? true : (strtolower($requireAuth) !== 'false' && $requireAuth !== '0');

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

function respond_json($data, int $code = 200, string $contentType = 'application/json') {
    http_response_code($code);
    header('Content-Type: ' . $contentType . '; charset=utf-8');
    echo is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

try {
    if ($path === '/api/openapi.json') {
        $gen = new OpenApiGenerator();
        $json = $gen->toJson($gen->build());
        respond_json($json, 200, 'application/json');
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
        $rows = $exec->run($query, $classes);

        $format = $_GET['format'] ?? ($body['format'] ?? 'json');
        if ($format === 'jsonld') {
            $collector = new ManifestCollector();
            $manifests = [];
            foreach ($rows as $r) {
                if (!empty($r['entity']) && class_exists($r['entity'])) {
                    $manifests[] = $collector->manifestFor($r['entity']);
                }
            }
            $jsonld = (new JsonLdExporter())->export($manifests);
            respond_json($jsonld, 200, 'application/ld+json');
        }
        respond_json($rows);
    }

    if ($path === '/api/ontology/entities') {
        $classes = isset($_GET['classes']) ? (array)$_GET['classes'] : [];
        $collector = new ManifestCollector();
        $out = [];
        foreach ($classes as $c) {
            if (class_exists($c)) $out[] = $collector->manifestFor($c);
        }
        respond_json($out);
    }

    if ($path === '/api/ontology/entity') {
        $name = $_GET['name'] ?? null;
        if (!$name || !class_exists($name)) respond_json(['error'=>'class not found'], 404);
        $collector = new ManifestCollector();
        respond_json($collector->manifestFor($name));
    }

    if ($path === '/api/ontology/traits') {
        $traits = array_filter(get_declared_traits(), fn($t) => str_starts_with($t, 'ObjectFoundation\\Traits\\'));
        respond_json(array_values($traits));
    }

    respond_json(['error'=>'Not found'], 404);
} catch (Throwable $e) {
    respond_json(['error'=>$e->getMessage()], 500);
}