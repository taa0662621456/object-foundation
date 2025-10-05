<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use ReflectionClass;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:locale:scan', description: 'Scan classes for Locale/Translatable traits presence.')]
final class FoundationLocaleScanCommand extends Command
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
            $ref = new ReflectionClass($class);
            $hasLocale = in_array('ObjectFoundation\\Traits\\LocaleAwareTrait', array_keys($ref->getTraits()), true);
            $hasTrans = in_array('ObjectFoundation\\Traits\\TranslatableTrait', array_keys($ref->getTraits()), true);

            $output->writeln(sprintf(
                "<info>%s</info> — LocaleAware: %s; Translatable: %s",
                $class,
                $hasLocale ? 'yes' : 'no',
                $hasTrans ? 'yes' : 'no'
            ));
        }

        return Command::SUCCESS;
    }
}
