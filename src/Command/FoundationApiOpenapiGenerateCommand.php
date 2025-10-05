<?php
namespace ObjectFoundation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ObjectFoundation\Api\OpenApiGenerator;

#[AsCommand(name: 'foundation:api:openapi:generate', description: 'Generate OpenAPI spec (JSON or YAML) for REST API.')]
final class FoundationApiOpenapiGenerateCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'json|yaml', 'json')
             ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output path', 'var/export/openapi.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = strtolower((string)$input->getOption('format'));
        $out = (string)$input->getOption('out');

        $gen = new OpenApiGenerator();
        $doc = $gen->build();
        $payload = $format === 'yaml' ? $gen->toYaml($doc) : $gen->toJson($doc);

        @mkdir(dirname($out), 0777, true);
        file_put_contents($out, $payload);
        $output->writeln("<info>OpenAPI written:</info> $out");
        return Command::SUCCESS;
    }
}