<?php
namespace ObjectFoundation\Ontology\Exporter;

final class JsonLdExporter
{
    public function export(array $manifests): string
    {
        $doc = [
            '@context' => [
                '@vocab' => 'https://schema.org/',
                'trait' => 'https://isponsor.dev/ontology/trait#',
                'interface' => 'https://isponsor.dev/ontology/interface#'
            ],
            '@graph' => []
        ];

        foreach ($manifests as $m) {
            $doc['@graph'][] = [
                '@type' => 'Thing',
                'name' => $m['entity'] ?? 'Unknown',
                'identifier' => $m['uuid'] ?? null,
                'additionalType' => 'OntologyEntity',
                'trait' => $m['traits'] ?? [],
                'interface' => $m['interfaces'] ?? [],
            ];
        }

        return json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}