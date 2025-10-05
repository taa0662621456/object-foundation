<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use ObjectFoundation\Ontology\Support\ManifestCollector;

#[AsCommand(name: 'foundation:ontology:inspect', description: 'Inspect ontology: traits, interfaces, properties for given classes.')]
final class FoundationOntologyInspectCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'FQCN list of entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classes = $input->getArgument('classes');
        $collector = new ManifestCollector();

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $output->writeln("<error>Class not found:</error> $class");
                continue;
            }
            $m = $collector->manifestFor($class);
            $ref = new \ReflectionClass($class);
            $props = array_map(fn($p) => '$'.$p->getName(), $ref->getProperties());

            $output->writeln("\n<info>{$m['entity']}</info>");
            $table = new Table($output);
            $table->setHeaders(['Aspect', 'Values']);
            $table->addRow(['Traits', implode('\n', $m['traits']) ?: '—']);
            $table->addRow(['Interfaces', implode('\n', $m['interfaces']) ?: '—']);
            $table->addRow(['Properties', implode('\n', $props) ?: '—']);
            $table->render();
        }

        return Command::SUCCESS;
    }
}
