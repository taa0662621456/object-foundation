<?php
namespace ObjectFoundation\Reaction;

use ObjectFoundation\Events\{EntityUpdatedEvent, EntityCreatedEvent};
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Ontology\Exporter\JsonLdExporter;

final readonly class AutoExportReaction
{
    public function __construct(private string $out = 'var/export/auto-entities.jsonld') {}

    public function __invoke(object $event): void
    {
        if (!($event instanceof EntityUpdatedEvent) && !($event instanceof EntityCreatedEvent)) return;

        $entity = $event->getEntity();
        $fqcn = get_class($entity);
        $collector = new ManifestCollector();
        $manifest = $collector->manifestFor($fqcn);

        $jsonld = (new JsonLdExporter())->export([$manifest]);
        @mkdir(dirname($this->out), 0777, true);
        file_put_contents($this->out, $jsonld);
    }
}
