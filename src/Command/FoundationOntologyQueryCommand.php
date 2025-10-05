<?php
namespace ObjectFoundation\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use ObjectFoundation\Ontology\Oql\{Parser, Executor};

#[AsCommand(name: 'foundation:ontology:query', description: 'Run an Ontology Query Language (OQL) statement against given classes.')]
final class FoundationOntologyQueryCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('query', InputArgument::REQUIRED, 'OQL statement, e.g. SELECT entity,traits WHERE has(LocaleAwareTrait) AND implements(TranslatableInterface)')
             ->addArgument('classes', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'FQCN list of entities')
             ->addOption('json', null, InputOption::VALUE_NONE, 'Output JSON instead of table');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statement = (string)$input->getArgument('query');
        $classes = $input->getArgument('classes');
        $asJson = (bool)$input->getOption('json');

        $parser = new Parser();
        $executor = new Executor();
        $q = $parser->parse($statement);
        $rows = $executor->run($q, $classes);

        if ($asJson) {
            $output->writeln(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return Command::SUCCESS;
        }

        // Tabular output
        $headers = array_map('trim', $q->select);
        if (empty($headers)) $headers = ['entity', 'traits', 'interfaces'];
        $table = new Table($output);
        $table->setHeaders($headers);
        foreach ($rows as $r) {
            $values = [];
            foreach ($headers as $h) {
                $key = strtolower($h);
                $val = $r[$key] ?? null;
                if (is_array($val)) $val = implode("\n", $val);
                $values[] = (string)($val ?? '');
            }
            $table->addRow($values);
        }
        $table->render();
        return Command::SUCCESS;
    }
}