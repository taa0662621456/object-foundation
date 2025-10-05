<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(name: 'foundation:traits:describe', description: 'Describe a trait (methods, properties)')]
final class FoundationTraitsDescribeCommand extends Command
{
    protected function configure(): void { $this->addArgument('trait', InputArgument::REQUIRED, 'FQCN of trait'); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string)$input->getArgument('trait');
        if (!trait_exists($name)) {
            $output->writeln("<error>Trait not found:</error> $name");
            return Command::FAILURE;
        }

        $ref = new \ReflectionClass($name);
        $methods = array_map(fn($m) => $m->getName(), $ref->getMethods());
        $props = array_map(fn($p) => '$'.$p->getName(), $ref->getProperties());
        $deps = array_keys($ref->getTraits());

        $table = new Table($output);
        $table->setHeaders(['Aspect', 'Values']);
        $table->addRow(['Methods', implode('\n', $methods) ?: '—']);
        $table->addRow(['Properties', implode('\n', $props) ?: '—']);
        $table->addRow(['Uses', implode('\n', $deps) ?: '—']);
        $table->render();
        return Command::SUCCESS;
    }
}
