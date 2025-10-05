<?php
namespace ObjectFoundation\Ontology\Exporter;

final class RdfExporter
{
    public function exportTurtle(array $manifests): string
    {
        $lines = [
            '@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .',
            '@prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .',
            '@prefix of: <https://isponsor.dev/ontology/> .',
            ''
        ];

        foreach ($manifests as $m) {
            $name = $m['entity'] ?? 'Unknown';
            $id = 'of:' . str_replace('\\', '/', $name);

            $lines[] = sprintf('%s rdf:type of:OntologyEntity ;', $id);

            foreach ($m['traits'] ?? [] as $t) {
                $lines[] = sprintf('    rdfs:seeAlso "%s" ;', $t);
            }

            foreach ($m['interfaces'] ?? [] as $i) {
                $lines[] = sprintf('    rdfs:subClassOf "%s" ;', $i);
            }

            $lines[] = '    .';
            $lines[] = '';
        }

        return implode("\n", $lines);
    }
}
