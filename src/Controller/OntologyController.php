<?php
declare(strict_types=1);

namespace ObjectFoundation\Controller;

use ObjectFoundation\Http\{Request, Response};
// NOTE: Replace with your real classes when available
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Cache\ManifestCache;
use ObjectFoundation\Service\{EtagService, OutboxEventService};

final class OntologyController
{
    public function entities(Request $req): Response
    {
        $classes = isset($req->query['classes']) ? (array)$req->query['classes'] : [];
        if (!class_exists(ManifestCollector::class) || !class_exists(ManifestCache::class)) {
            return Response::json(['entities' => [], 'note' => 'Ontology components not wired'], 200);
        }

        $collector = new ManifestCollector();
        $cache = new ManifestCache();
        $out = [];
        foreach ($classes as $c) {
            if (class_exists($c)) {
                $out[] = $cache->manifestFor($c, $collector);
            }
        }
        $payload = json_encode($out, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $resp = Response::json($payload);
        $resp = (new EtagService())->withEtag($req, $payload, time(), $resp);

        if ($req->method === 'POST') {
            (new OutboxEventService())->append('OntologyEntitiesFetched', ['classes' => $classes, 'count' => count($out)]);
        }
        return $resp;
    }

    public function entity(Request $req): Response
    {
        $name = $req->query['name'] ?? null;
        if (!$name || !class_exists($name)) {
            return Response::json(['error' => 'class not found'], 404);
        }
        if (!class_exists(ManifestCollector::class) || !class_exists(ManifestCache::class)) {
            return Response::json(['entity' => [], 'note' => 'Ontology components not wired'], 200);
        }
        $collector = new ManifestCollector();
        $cache = new ManifestCache();
        $manifest = $cache->manifestFor($name, $collector);
        $payload = json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $resp = Response::json($payload);
        $resp = (new EtagService())->withEtag($req, $payload, time(), $resp);

        if ($req->method === 'POST') {
            (new OutboxEventService())->append('OntologyEntityFetched', ['name' => $name]);
        }
        return $resp;
    }

    public function traits(Request $req): Response
    {
        $traits = array_filter(get_declared_traits(), fn($t) => str_starts_with($t, 'ObjectFoundation\\Traits\\'));
        return Response::json(array_values($traits));
    }
}
