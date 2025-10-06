<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:api:key:generate', description: 'Generate a random API key and print ENV line')]
final class FoundationApiKeyGenerateCommand extends Command
{
    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $output->writeln("<info>API Key:</info> $key");
        $output->writeln("Add to env:");
        $output->writeln("OBJECT_FOUNDATION_API_KEYS=$key");
        return Command::SUCCESS;
    }
}
