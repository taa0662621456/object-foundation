<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use ObjectFoundation\Ontology\Exporter\JsonLdExporter;
use ObjectFoundation\Ontology\Oql\Executor;
use ObjectFoundation\Ontology\Oql\Parser;
use ObjectFoundation\Ontology\Support\ManifestCollector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'foundation:ontology:query',
    description: 'Run an OQL statement against given classes or a class list file. Supports OR, NOT, parentheses.'
)]
final class FoundationOntologyQueryCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument(
                'query',
                InputArgument::REQUIRED,
                'OQL statement, e.g. SELECT entity,traits WHERE has(LocaleAwareTrait) OR NOT implements(LegacyInterface)'
            )
            ->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'FQCN list')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'JSON file with array of FQCN')
            ->addOption('format', 'F', InputOption::VALUE_REQUIRED, 'table|json|jsonld', 'table')
            ->addOption('export', 'o', InputOption::VALUE_REQUIRED, 'Output file for JSON/JSON-LD')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit results', '0')
            ->addOption('sort', 's', InputOption::VALUE_REQUIRED, 'Sort by field (entity|traits|interfaces)', 'entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statement = (string) $input->getArgument('query');
        $classes = $input->getArgument('classes') ?? [];

        // Load classes from file if provided
        $from = $input->getOption('from');
        if ($from) {
            if (!is_file($from)) {
                $output->writeln("<error>File not found:</error> $from");
                return Command::FAILURE;
            }
            $json = json_decode(file_get_contents($from), true);
            if (!is_array($json)) {
                $output->writeln("<error>Invalid JSON class list</error>");
                return Command::FAILURE;
            }
            $classes = array_merge($classes, $json);
        }
        $classes = array_values(array_unique($classes));

        $parser = new Parser();
        $executor = new Executor();
        $q = $parser->parse($statement);
        $rows = $executor->run($q, $classes);

        // Sorting
        $sort = strtolower((string) $input->getOption('sort'));
        usort($rows, function ($a, $b) use ($sort) {
            $va = $a[$sort] ?? '';
            $vb = $b[$sort] ?? '';
            if (is_array($va)) $va = json_encode($va);
            if (is_array($vb)) $vb = json_encode($vb);
            return strcmp((string) $va, (string) $vb);
        });

        // Limit
        $limit = (int) $input->getOption('limit');
        if ($limit > 0) {
            $rows = array_slice($rows, 0, $limit);
        }

        $format = strtolower((string) $input->getOption('format'));
        $export = $input->getOption('export');

        // Export JSON-LD
        if ($format === 'jsonld' || ($export && str_ends_with(strtolower($export), '.jsonld'))) {
            $collector = new ManifestCollector();
            $manifests = [];
            foreach ($rows as $r) {
                $fqcn = $r['entity'] ?? null;
                if ($fqcn && class_exists($fqcn)) {
                    $manifests[] = $collector->manifestFor($fqcn);
                }
            }
            $exporter = new JsonLdExporter();
            $json = $exporter->export($manifests);
            return $this->writeExport($output, $export, $json, 'JSON-LD');
        }

        // Export JSON
        if ($format === 'json' || ($export && str_ends_with(strtolower((string) $export), '.json'))) {
            $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            return $this->writeExport($output, $export, $json, 'JSON');
        }

        // Default: table
        $headers = $q->select ?: ['entity', 'traits', 'interfaces'];
        $table = new Table($output);
        $table->setHeaders($headers);
        foreach ($rows as $r) {
            $row = [];
            foreach ($headers as $h) {
                $key = strtolower(trim($h));
                $val = $r[$key] ?? '';
                if (is_array($val)) {
                    $val = implode("\n", $val);
                }
                $row[] = (string) $val;
            }
            $table->addRow($row);
        }
        $table->render();

        // Optional export placeholder for text
        if ($export) {
            $dir = dirname($export);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            file_put_contents($export, "See console output for table. Use --format json or jsonld for file export.\n");
            $output->writeln("<comment>Non-JSON export writes placeholder. Use --format json/jsonld for real export.</comment>");
        }

        return Command::SUCCESS;
    }

    private function writeExport(OutputInterface $output, ?string $path, string $content, string $label): int
    {
        if ($path) {
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            file_put_contents($path, $content);
            $output->writeln("<info>Exported {$label}:</info> {$path}");
        } else {
            $output->writeln($content);
        }

        return Command::SUCCESS;
    }
}
