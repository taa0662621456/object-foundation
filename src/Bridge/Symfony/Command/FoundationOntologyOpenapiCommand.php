<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use stdClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ObjectFoundation\Ontology\Support\ManifestCollector;

#[AsCommand(
    name: 'foundation:ontology:openapi',
    description: 'Generate a basic OpenAPI 3.0 YAML from ontology manifests.'
)]
final class FoundationOntologyOpenapiCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'FQCN list of entities')
            ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output openapi.yaml path', 'var/export/openapi.yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classes = $input->getArgument('classes');
        $out = (string) $input->getOption('out');

        $collector = new ManifestCollector();
        $schemas = [];

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $output->writeln("<error>Class not found:</error> $class");
                continue;
            }

            $m = $collector->manifestFor($class);
            $name = str_replace('\\', '_', $m['entity']);

            $schemas[$name] = [
                'type' => 'object',
                'properties' => [
                    'id'         => ['type' => 'integer'],
                    'uuid'       => ['type' => 'string'],
                    'slug'       => ['type' => 'string'],
                    'createdAt'  => ['type' => 'string', 'format' => 'date-time'],
                    'updatedAt'  => ['type' => 'string', 'format' => 'date-time'],
                    'published'  => ['type' => 'boolean'],
                    'version'    => ['type' => 'integer'],
                ],
            ];
        }

        $doc = [
            'openapi' => '3.0.3',
            'info' => [
                'title'   => 'Object Foundation API',
                'version' => '1.0.0',
            ],
            'paths' => new stdClass(),
            'components' => ['schemas' => $schemas],
        ];

        $yaml = self::yaml($doc);

        $dir = dirname($out);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        file_put_contents($out, $yaml);
        $output->writeln("<info>Generated OpenAPI spec:</info> $out");

        return Command::SUCCESS;
    }

    private static function yaml(array $a, int $indent = 0): string
    {
        $out = '';
        foreach ($a as $k => $v) {
            $pad = str_repeat('  ', $indent);
            if (is_array($v)) {
                $out .= $pad . $k . ":\n" . self::yaml($v, $indent + 1);
            } elseif ($v instanceof stdClass) {
                $out .= $pad . $k . ": {}\n";
            } else {
                $val = is_bool($v) ? ($v ? 'true' : 'false') : (string) $v;
                $out .= $pad . $k . ': ' . $val . "\n";
            }
        }
        return $out;
    }
}
