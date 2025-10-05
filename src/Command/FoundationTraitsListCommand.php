<?php
namespace ObjectFoundation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

#[AsCommand(name: 'foundation:traits:list', description: 'List ObjectFoundation traits available in the library')]
final class FoundationTraitsListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $traits = array_filter(get_declared_traits(), fn($t) => str_starts_with($t, 'ObjectFoundation\\Traits\\'));

        $table = new Table($output);
        $table->setHeaders(['Trait']);
        foreach ($traits as $t) $table->addRow([$t]);
        $table->render();

        // JSON export
        @mkdir('var/export', 0777, true);
        file_put_contents('var/export/traits.json', json_encode(array_values($traits), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $output->writeln('<info>Saved:</info> var/export/traits.json');
        return Command::SUCCESS;
    }
}