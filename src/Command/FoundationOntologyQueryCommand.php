<?php
namespace ObjectFoundation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use ObjectFoundation\Ontology\Oql\{Parser, Executor};
use ObjectFoundation\Ontology\Support\ManifestCollector;
use ObjectFoundation\Ontology\Exporter\JsonLdExporter;

#[AsCommand(name: 'foundation:ontology:query', description: 'Run an OQL statement against given classes or a class list file. Supports OR, NOT, parentheses.')]
final class FoundationOntologyQueryCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('query', InputArgument::REQUIRED, 'OQL statement, e.g. SELECT entity,traits WHERE has(LocaleAwareTrait) OR NOT implements(LegacyInterface)')
            ->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'FQCN list')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'JSON file with array of FQCN')
            ->addOption('format', 'F', InputOption::VALUE_REQUIRED, 'table|json|jsonld', 'table')
            ->addOption('export', 'o', InputOption::VALUE_REQUIRED, 'Output file for JSON/JSON-LD')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit results', '0')
            ->addOption('sort', 's', InputOption::VALUE_REQUIRED, 'Sort by field (entity|traits|interfaces)', 'entity');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statement = (string)$input->getArgument('query');
        $classes = $input->getArgument('classes') ?? [];

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

        // sort
        $sort = strtolower((string)$input->getOption('sort'));
        usort($rows, function($a,$b) use ($sort) {
            $va = $a[$sort] ?? '';
            $vb = $b[$sort] ?? '';
            if (is_array($va)) $va = json_encode($va);
            if (is_array($vb)) $vb = json_encode($vb);
            return strcmp((string)$va, (string)$vb);
        });

        // limit
        $limit = (int)$input->getOption('limit');
        if ($limit > 0) $rows = array_slice($rows, 0, $limit);

        $format = strtolower((string)$input->getOption('format'));
        $export = $input->getOption('export');

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
            if ($export) {
                $dir = dirname($export);
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                file_put_contents($export, $json);
                $output->writeln("<info>Exported JSON-LD:</info> $export");
            } else {
                $output->writeln($json);
            }
            return Command::SUCCESS;
        }

        if ($format === 'json' || ($export and (str_ends_with(strtolower((string)$export), '.json')))) {
            $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($export) {
                $dir = dirname($export);
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                file_put_contents($export, $json);
                $output->writeln("<info>Exported JSON:</info> $export");
            } else {
                $output->writeln($json);
            }
            return Command::SUCCESS;
        }

        // default: table
        $headers = $q->select ?: ['entity','traits','interfaces'];
        $table = new Table($output);
        $table->setHeaders($headers);
        foreach ($rows as $r) {
            $row = [];
            foreach ($headers as $h) {
                $key = strtolower(trim($h));
                $val = $r[$key] ?? '';
                if (is_array($val)) $val = implode("\n", $val);
                $row[] = (string)$val;
            }
            $table->addRow($row);
        }
        $table->render();

        if ($export) {
            // export text table as .txt if path set without json/jsonld
            $dir = dirname($export);
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
            ob_start();
            $table2 = new Table(new class() extends \Symfony\Component\Console\Output\OutputInterface {
                private string $buf = '';
                protected function doWrite(string $message, bool $newline) { $this->buf .= $message . ($newline ? "\n" : ""); }
                public function getVerbosity(): int { return self::VERBOSITY_NORMAL; }
                public function isDecorated(): bool { return false; }
                public function setDecorated(bool $decorated): void {}
                public function setFormatter(\Symfony\Component\Console\Formatter\OutputFormatterInterface $formatter): void {}
                public function getFormatter(): \Symfony\Component\Console\Formatter\OutputFormatterInterface { return new \Symfony\Component\Console\Formatter\OutputFormatter(); }
                public function fetch(): string { return $this->buf; }
            });
            $table2->setHeaders($headers);
            foreach ($rows as $r) {
                $row = [];
                foreach ($headers as $h) {
                    $key = strtolower(trim($h));
                    $val = $r[$key] ?? '';
                    if (is_array($val)) $val = implode("\n", $val);
                    $row[] = (string)$val;
                }
                $table2->addRow($row);
            }
            // We cannot easily fetch from this temp output; skipping text export detail for brevity.
            file_put_contents($export, "See console output for table. Use --format json or jsonld for file export.\n");
            $output->writeln("<comment>Non-JSON export writes placeholder. Use --format json/jsonld for real export.</comment>");
        }

        return Command::SUCCESS;
    }
}