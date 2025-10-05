<?php
// Minimal router for PHP built-in server:
// php -S 127.0.0.1:8080 -t public public/index.php

require __DIR__ . '/../vendor/autoload.php';

use ObjectFoundation\Ontology\Oql\{Parser, Executor};
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Ontology\Exporter\JsonLdExporter;

header('Content-Type: application/json; charset=utf-8');

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

function read_json() {
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    exit;
}

try {
    if ($path === '/api/oql') {
        $q = $_GET['query'] ?? null;
        $classes = isset($_GET['classes']) ? (array)$_GET['classes'] : [];
        if ($method === 'POST') {
            $body = read_json();
            $q = $body['query'] ?? $q;
            $classes = $body['classes'] ?? $classes;
        }
        if (!$q) respond(['error' => 'Missing query'], 400);
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
            header('Content-Type: application/ld+json');
            echo $jsonld; exit;
        }
        respond($rows);
    }

    if ($path === '/api/ontology/entities') {
        // returns list of entities passed via ?classes[]=...
        $classes = isset($_GET['classes']) ? (array)$_GET['classes'] : [];
        $collector = new ManifestCollector();
        $out = [];
        foreach ($classes as $c) {
            if (class_exists($c)) $out[] = $collector->manifestFor($c);
        }
        respond($out);
    }

    if ($path === '/api/ontology/entity') {
        $name = $_GET['name'] ?? null;
        if (!$name || !class_exists($name)) respond(['error'=>'class not found'], 404);
        $collector = new ManifestCollector();
        respond($collector->manifestFor($name));
    }

    if ($path === '/api/ontology/traits') {
        $traits = array_filter(get_declared_traits(), fn($t) => str_starts_with($t, 'ObjectFoundation\\Traits\\'));
        respond(array_values($traits));
    }

    respond(['error' => 'Not found'], 404);
} catch (Throwable $e) {
    respond(['error' => $e->getMessage()], 500);
}