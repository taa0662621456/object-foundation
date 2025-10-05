<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Ontology\Exporter\JsonLdExporter;

#[AsCommand(name: 'foundation:ontology:export', description: 'Export ontology to JSON-LD (and optionally RDF in future).')]
final class FoundationOntologyExportCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'FQCN list of entities')
             ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'jsonld|rdf', 'jsonld')
             ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output path', 'var/export/ontology.jsonld');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classes = $input->getArgument('classes');
        $format = strtolower((string)$input->getOption('format'));
        $out = (string)$input->getOption('out');

        $collector = new ManifestCollector();
        $manifests = [];
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $output->writeln("<error>Class not found:</error> $class");
                continue;
            }
            $$manifests[] =($collector->manifestFor($class));
        }

        if ($format !== 'jsonld') {
            $output->writeln('<comment>Only JSON-LD implemented for now; using jsonld.</comment>');
        }
        $exporter = new JsonLdExporter();
        $json = $exporter->export($manifests);
        $dir = dirname($out);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        file_put_contents($out, $json);
        $output->writeln("<info>Exported:</info> $out");

        return Command::SUCCESS;
    }
}
