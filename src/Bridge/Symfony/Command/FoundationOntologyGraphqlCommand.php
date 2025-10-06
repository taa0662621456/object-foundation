<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use ObjectFoundation\Ontology\Support\ManifestCollector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'foundation:ontology:graphql',
    description: 'Generate a basic GraphQL schema from ontology manifests.'
)]
final class FoundationOntologyGraphqlCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'FQCN list of entities')
            ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output .graphql path', 'var/export/schema.graphql');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classes = $input->getArgument('classes');
        $out = (string) $input->getOption('out');

        $collector = new ManifestCollector();
        $types = [];

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $output->writeln("<error>Class not found:</error> $class");
                continue;
            }

            $m = $collector->manifestFor($class);
            $name = str_replace('\\', '_', $m['entity']);

            $fields = [
                'id: ID',
                'uuid: String',
                'slug: String',
                'createdAt: String',
                'updatedAt: String',
                'published: Boolean',
                'version: Int'
            ];

            $body = "    " . implode("\n    ", $fields);
            $types[] = "type {$name} {\n{$body}\n}";
        }

        $schema = implode("\n\n", $types);

        $dir = dirname($out);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        file_put_contents($out, $schema);
        $output->writeln("<info>Generated GraphQL schema:</info> $out");

        return Command::SUCCESS;
    }
}
