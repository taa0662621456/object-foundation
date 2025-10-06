<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use ObjectFoundation\Api\Observability\MetricsCollector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:metrics:report', description: 'Print aggregated API metrics snapshot')]
final class FoundationMetricsReportCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mc = new MetricsCollector();
        $snap = $mc->snapshot();
        $lines = [];
        $lines[] = "Total requests: " . (int)($snap['count_total'] ?? 0);
        $lines[] = "Avg latency: " . number_format((float)($snap['avg_latency'] ?? 0), 2) . " ms";
        $status = $snap['status'] ?? [];
        foreach ($status as $code => $cnt) $lines[] = "$code: $cnt";
        $total = max(1, (int)($snap['count_total'] ?? 1));
        $cache = (int)($snap['cache_hit'] ?? 0);
        $lines[] = "Cache hits: " . number_format(100.0 * $cache / $total, 2) . "%";
        $lines[] = "Auth failures: " . (int)($snap['auth_fail'] ?? 0);
        $output->writeln(implode(PHP_EOL, $lines));
        return Command::SUCCESS;
    }
}
