<?php
declare(strict_types=1);

namespace ObjectFoundation\Controller;

use ObjectFoundation\Http\{Request, Response};
// NOTE: Replace with your actual MetricsCollector class if present
use ObjectFoundation\Api\Observability\MetricsCollector;

final class MetricsController
{

    public function handle(Request $req): Response
    {
        if (class_exists(MetricsCollector::class)) {
            $mc = new MetricsCollector();
            $snapshot = $mc->snapshot();
            $export = filter_var((string) getenv('OBJECT_FOUNDATION_METRICS_EXPORT'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $export = $export ?? true;
            $format = $req->query['format'] ?? null;
            if ($export && $format === 'prometheus') {
                return Response::text($mc->toPrometheus($snapshot));
            }
            return Response::json($snapshot);
        }
        return Response::json(['metrics' => 'not-configured']);
    }
}
