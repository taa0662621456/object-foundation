<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ObjectFoundation\Events\{OutboxStorage, WebhookDispatcher};

#[AsCommand(name: 'foundation:outbox:dispatch', description: 'Dispatch all pending outbox events to configured webhooks')]
final class FoundationOutboxDispatchCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outbox = new OutboxStorage();
        $hooks = new WebhookDispatcher();
        $list = $outbox->allUndispatched(1000);
        $ok = 0; $fail = 0;
        foreach ($list as $rec) {
            $sent = $hooks->dispatch($rec);
            if ($sent) {
                $outbox->markDispatched($rec['id']);
                $ok++;
            } else {
                $outbox->incrementAttempt($rec['id'], 'dispatch failed');
                $fail++;
            }
        }
        $output->writeln("<info>Dispatched:</info> $ok, <comment>Failed:</comment> $fail");
        return Command::SUCCESS;
    }
}
