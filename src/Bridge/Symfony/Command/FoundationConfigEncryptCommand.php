<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:config:encrypt', description: 'Encrypt a JSON config with a key (prints base64 payload).')]
final class FoundationConfigEncryptCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('key', InputArgument::REQUIRED, 'Encryption key')
             ->addArgument('json', InputArgument::REQUIRED, 'JSON string to encrypt');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = (string)$input->getArgument('key');
        $json = (string)$input->getArgument('json');
        $cipher = 'AES-256-CBC';
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivlen);
        $ct = openssl_encrypt($json, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $payload = base64_encode($iv . $ct);
        $output->writeln($payload);
        return Command::SUCCESS;
    }
}
