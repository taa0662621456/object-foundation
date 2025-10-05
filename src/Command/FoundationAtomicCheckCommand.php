<?php
namespace ObjectFoundation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:atomic:check', description: 'Verify atomic integrity: core must not depend on framework namespaces')]
final class FoundationAtomicCheckCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Source path to scan', 'src')
             ->addOption('allow', 'a', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Allowed namespace prefixes', [
                 'ObjectFoundation\\',
                 'Doctrine\\',
                 'Psr\\',
                 'Symfony\\Contracts\\',  // allow contracts (lightweight)
             ])
             ->addOption('forbid', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Forbidden namespace prefixes', [
                 'Symfony\\Component\\',
                 'Illuminate\\',
                 'Laravel\\',
             ])
             ->addOption('exclude', 'e', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Exclude directories (glob)', [
                 'src/Bridge/*',
                 'src/SDK/*',
                 'vendor/*'
             ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $base = (string)$input->getOption('path');
        $allow = $input->getOption('allow') ?? [];
        $forbid = $input->getOption('forbid') ?? [];
        $exclude = $input->getOption('exclude') ?? [];

        $files = $this->collectPhp($base, $exclude);
        $violations = [];

        foreach ($files as $file) {
            $code = file_get_contents($file) ?: '';
            // scan "use X\Y\Z;" and fully qualified "new X\Y\Z(" patterns
            if (preg_match_all('/\buse\s+([A-Za-z0-9_\\\\]+)\s*;/m', $code, $m)) {
                foreach ($m[1] as $ns) {
                    if ($this->isForbidden($ns, $forbid, $allow)) {
                        $violations[] = [$file, $ns, 'use'];
                    }
                }
            }
            if (preg_match_all('/new\s+\\?([A-Za-z0-9_\\\\]+)\s*\(/m', $code, $m2)) {
                foreach ($m2[1] as $ns) {
                    if ($this->isForbidden($ns, $forbid, $allow)) {
                        $violations[] = [$file, $ns, 'new'];
                    }
                }
            }
        }

        if ($violations) {
            $output->writeln('<error>Atomic integrity violations found:</error>');
            foreach ($violations as [$file, $ns, $kind]) {
                $output->writeln(" - {$file}: {$kind} {$ns}");
            }
            return Command::FAILURE;
        }

        $output->writeln('<info>Atomic integrity: OK</info>');
        return Command::SUCCESS;
    }

    private function collectPhp(string $base, array $exclude): array
    {
        $out = [];
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base));
        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            $path = str_replace('\\', '/', $file->getPathname());
            if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') continue;
            $skip = false;
            foreach ($exclude as $gl) {
                $pattern = '#^' . str_replace(['*'], ['.*'], str_replace('/', '\/', $gl)) . '$#';
                if (preg_match($pattern, $path)) { $skip = true; break; }
            }
            if (!$skip) $out[] = $path;
        }
        return $out;
    }

    private function isForbidden(string $ns, array $forbid, array $allow): bool
    {
        foreach ($allow as $ok) {
            if (str_starts_with($ns, $ok)) return false;
        }
        foreach ($forbid as $bad) {
            if (str_starts_with($ns, $bad)) return true;
        }
        return false;
    }
}