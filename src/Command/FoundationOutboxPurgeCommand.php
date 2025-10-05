<?php
namespace ObjectFoundation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ObjectFoundation\Events\OutboxStorage;

#[AsCommand(name: 'foundation:outbox:purge', description: 'Purge dispatched events from outbox storage')]
final class FoundationOutboxPurgeCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outbox = new OutboxStorage();
        $n = $outbox->purgeDispatched();
        $output->writeln("<info>Purged:</info> $n dispatched events");
        return Command::SUCCESS;
    }
}