<?php
declare(strict_types=1);

namespace ObjectFoundation\Controller;

use ObjectFoundation\Http\{Request, Response};
// NOTE: Replace with your real classes when available
use ObjectFoundation\Ontology\Oql\{Parser, Executor};
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Ontology\Exporter\JsonLdExporter;
use ObjectFoundation\Cache\ManifestCache;
use ObjectFoundation\Service\{EtagService, OutboxEventService};

final class OqlController
{
    public function handle(Request $req): Response
    {
        $q = $req->query['query'] ?? null;
        $classes = isset($req->query['classes']) ? (array)$req->query['classes'] : [];
        if ($req->method === 'POST') {
            $q = $req->body['query'] ?? $q;
            $classes = $req->body['classes'] ?? $classes;
        }
        if (!$q) {
            return Response::json(['error' => 'Missing query'], 400);
        }

        if (!class_exists(Parser::class) || !class_exists(Executor::class) || !class_exists(ManifestCache::class)) {
            return Response::json(['rows' => [], 'note' => 'OQL components not wired'], 200);
        }

        $parser = new Parser();
        $query = $parser->parse($q);
        $exec = new Executor();
        $cache = new ManifestCache();

        $cacheKey = 'oql:' . sha1($q . '|' . json_encode($classes));
        $rows = $cache->result($cacheKey, fn() => $exec->run($query, $classes));

        $format = $req->query['format'] ?? ($req->body['format'] ?? 'json');

        if ($format === 'jsonld') {
            $collector = new ManifestCollector();
            $manifests = [];
            foreach ($rows as $r) {
                if (!empty($r['entity']) && class_exists($r['entity'])) {
                    $manifests[] = (new ManifestCache())->manifestFor($r['entity'], $collector);
                }
            }
            $jsonld = (new JsonLdExporter())->export($manifests);
            $resp = Response::json($jsonld, 200, ['Content-Type' => 'application/ld+json; charset=utf-8']);
            $resp = (new EtagService())->withEtag($req, $jsonld, time(), $resp);

            if ($req->method === 'POST') {
                (new OutboxEventService())->append('OQLQueryExecuted', ['query' => $q, 'classes' => $classes, 'rows' => count($rows)]);
            }
            return $resp;
        }

        $payload = json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $resp = Response::json($payload);
        $resp = (new EtagService())->withEtag($req, $payload, time(), $resp);

        if ($req->method === 'POST') {
            (new OutboxEventService())->append('OQLQueryExecuted', ['query' => $q, 'classes' => $classes, 'rows' => count($rows)]);
        }
        return $resp;
    }
}
