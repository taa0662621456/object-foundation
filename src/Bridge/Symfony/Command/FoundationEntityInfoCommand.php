<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(name: 'foundation:entity:info', description: 'Prints ontology info about given entity classes')]
final class FoundationEntityInfoCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'FQCN list of entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $classes = $input->getArgument('classes');

        foreach ($classes as $class) {
            if (!class_exists($class)) {
                $output->writeln("<error>Class not found:</error> $class");
                continue;
            }
            $ref = new \ReflectionClass($class);
            $traits = array_keys($ref->getTraits());
            $interfaces = array_values($ref->getInterfaceNames());
            $props = array_map(fn($p) => '$'.$p->getName(), $ref->getProperties());

            $output->writeln("\n<info>{$ref->getName()}</info>");
            $table = new Table($output);
            $table->setHeaders(['Aspect', 'Values']);
            $table->addRow(['Traits', implode('\n', $traits) ?: '—']);
            $table->addRow(['Interfaces', implode('\n', $interfaces) ?: '—']);
            $table->addRow(['Properties', implode('\n', $props) ?: '—']);
            $table->render();
        }

        return Command::SUCCESS;
    }
}
