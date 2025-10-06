<?php
namespace ObjectFoundation\Ontology\Support;

use ReflectionClass;

final class ManifestCollector
{
    /**
     * Build a manifest entry for the given FQCN via reflection.
     * @throws \ReflectionException
     */
    public function manifestFor(string $class): array
    {
        $ref = new ReflectionClass($class);
        $traits = array_keys($ref->getTraits());
        $interfaces = array_values($ref->getInterfaceNames());
        return [
            'entity' => $ref->getName(),
            'traits' => $traits,
            'interfaces' => $interfaces,
        ];
    }
}
