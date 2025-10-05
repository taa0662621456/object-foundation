<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:entity:init', description: 'Scaffold a Doctrine Entity with Object Foundation traits.')]
final class FoundationEntityInitCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Entity class name (without namespace)')
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Namespace for entity', 'App\\Entity')
            ->addOption('traits', null, InputOption::VALUE_REQUIRED, 'Comma-separated traits to include', 'EntityFoundationTrait')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Output directory', 'src/Entity')
            ->addOption('with-events', null, InputOption::VALUE_NONE, 'Add basic event hooks annotations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = preg_replace('/[^A-Za-z0-9_]/', '', (string)$input->getArgument('name'));
        $ns = (string)$input->getOption('namespace');
        $traitsArg = (string)$input->getOption('traits');
        $dir = rtrim((string)$input->getOption('path'), '/');
        $withEvents = (bool)$input->getOption('with-events');

        $fqcn = $ns . '\\' . $name;
        $useTraits = array_map('trim', explode(',', $traitsArg));
        $useLines = ["use Doctrine\\ORM\\Mapping as ORM;"];
        $traitLines = [];
        foreach ($useTraits as $t) {
            $t = trim($t);
            if ($t === '') continue;
            $useLines[] = "use ObjectFoundation\\Traits\\$t;";
            $traitLines[] = "    use $t;";
        }
        $use = implode("\n", array_unique($useLines));
        $traits = implode("\n", $traitLines);

        $events = $withEvents ? "\n#[ORM\\HasLifecycleCallbacks]" : '';
        $php = <<<PHP
<?php
namespace $ns;

$use

#[ORM\\Entity]$events
class $name
{
$traits
}
PHP;

        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        $file = rtrim($dir, '/') . '/' . $name . '.php';
        file_put_contents($file, $php);
        $output->writeln("<info>Created:</info> $file");
        $output->writeln("<comment>FQCN:</comment> $fqcn");
        return Command::SUCCESS;
    }
}
