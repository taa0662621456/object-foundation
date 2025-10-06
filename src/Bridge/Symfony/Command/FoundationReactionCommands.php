<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use ObjectFoundation\Reaction\{AutoExportReaction, AutoLogReaction, ReactionRegistry};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:reaction:list', description: 'List registered reactions and their states')]
final class FoundationReactionListCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $registry = self::bootstrap();
        $rows = $registry->list();

        $table = new Table($output);
        $table->setHeaders(['Reaction', 'Enabled']);
        foreach ($rows as $r) $table->addRow([$r['name'], $r['enabled'] ? 'yes' : 'no']);
        $table->render();
        return Command::SUCCESS;
    }

    public static function bootstrap(): ReactionRegistry
    {
        $registry = new ReactionRegistry();
        $registry->register('AutoLogReaction', new AutoLogReaction());
        $registry->register('AutoExportReaction', new AutoExportReaction());
        return $registry;
    }
}

#[AsCommand(name: 'foundation:reaction:enable', description: 'Enable a reaction by name')]
final class FoundationReactionEnableCommand extends Command
{
    protected function configure(): void { $this->addArgument('name', InputArgument::REQUIRED); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $registry = \ObjectFoundation\Bridge\Symfony\Command\FoundationReactionListCommand::bootstrap();
        $name = (string)$input->getArgument('name');
        $registry->enable($name);
        $output->writeln("<info>Enabled:</info> $name");
        return Command::SUCCESS;
    }
}

#[AsCommand(name: 'foundation:reaction:disable', description: 'Disable a reaction by name')]
final class FoundationReactionDisableCommand extends Command
{
    protected function configure(): void { $this->addArgument('name', InputArgument::REQUIRED); }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $registry = FoundationReactionListCommand::bootstrap();
        $name = (string)$input->getArgument('name');
        $registry->disable($name);
        $output->writeln("<info>Disabled:</info> $name");
        return Command::SUCCESS;
    }
}
