<?php
declare(strict_types=1);

namespace ObjectFoundation\Http;

use ObjectFoundation\Controller\{DocsController, MetricsController, OqlController, OntologyController, NotFoundController};

final class Router
{
    public function dispatch(Request $req): Response
    {
        return match ($req->path) {
            '/api/metrics'           => (new MetricsController())->handle($req),
            '/api/openapi.json'      => (new DocsController())->openapi($req),
            '/api/docs'              => (new DocsController())->swagger($req),
            '/api/oql'               => (new OqlController())->handle($req),
            '/api/ontology/entities' => (new OntologyController())->entities($req),
            '/api/ontology/entity'   => (new OntologyController())->entity($req),
            '/api/ontology/traits'   => (new OntologyController())->traits($req),
            default                  => (new NotFoundController())->handle($req),
        };
    }
}
