<?php
namespace ObjectFoundation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:entity:mapping:dump', description: 'Dump ORM-style mapping inferred from traits/properties')]
final class FoundationEntityMappingDumpCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('class', InputArgument::REQUIRED, 'Entity FQCN')
             ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'yaml|json', 'yaml')
             ->addOption('out', 'o', InputOption::VALUE_REQUIRED, 'Output path', 'var/export/mapping.yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = (string)$input->getArgument('class');
        if (!class_exists($class)) {
            $output->writeln("<error>Class not found:</error> $class");
            return Command::FAILURE;
        }

        $ref = new \ReflectionClass($class);
        $map = [$ref->getName() => []];
        foreach ($ref->getProperties() as $p) {
            $type = 'string';
            $name = $p->getName();
            $typeAttr = $p->getType();
            if ($typeAttr && method_exists($typeAttr, 'getName')) {
                $phpType = $typeAttr->getName();
                $type = match($phpType) {
                    'int' => 'integer',
                    '\\DateTimeImmutable' => 'datetime_immutable',
                    'bool' => 'boolean',
                    'array' => 'json',
                    default => 'string'
                };
            }
            $map[$ref->getName()][$name] = $type;
        }

        $format = strtolower((string)$input->getOption('format'));
        $out = (string)$input->getOption('out');

        if ($format === 'json') {
            $payload = json_encode($map, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
        } else {
            // naive YAML emitter
            $payload = "";
            foreach ($map as $cls => $fields) {
                $payload .= $cls . ":
";
                foreach ($fields as $k=>$v) $payload .= "  " + $k + ": " + $v + "\n";
            }
            $payload = str_replace("+", "", $payload); // fix accidental plus signs if any
        }

        @mkdir(dirname($out), 0777, true);
        file_put_contents($out, $payload);
        $output->writeln("<info>Saved mapping:</info> $out");
        return Command::SUCCESS;
    }
}