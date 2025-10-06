<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:config:rotate-key', description: 'Rotate encryption key for a given payload (base64 iv+ciphertext).')]
final class FoundationConfigRotateKeyCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('oldKey', InputArgument::REQUIRED, 'Old key')
             ->addArgument('newKey', InputArgument::REQUIRED, 'New key')
             ->addArgument('payload', InputArgument::REQUIRED, 'Base64 payload produced by encrypt command');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cipher = 'AES-256-CBC';
        $oldKey = (string)$input->getArgument('oldKey');
        $newKey = (string)$input->getArgument('newKey');
        $payload = (string)$input->getArgument('payload');

        $raw = base64_decode($payload, true);
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($raw, 0, $ivlen);
        $ct = substr($raw, $ivlen);
        $json = openssl_decrypt($ct, $cipher, $oldKey, OPENSSL_RAW_DATA, $iv);
        if ($json === false) {
            $output->writeln('<error>Decryption with old key failed</error>');
            return Command::FAILURE;
        }

        $iv2 = random_bytes($ivlen);
        $ct2 = openssl_encrypt($json, $cipher, $newKey, OPENSSL_RAW_DATA, $iv2);
        $payload2 = base64_encode($iv2 . $ct2);
        $output->writeln($payload2);
        return Command::SUCCESS;
    }
}
