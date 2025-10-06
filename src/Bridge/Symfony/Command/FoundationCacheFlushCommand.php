<?php
namespace ObjectFoundation\Bridge\Symfony\Command;

use ObjectFoundation\Cache\{FileCacheAdapter, ManifestCache, RedisCacheAdapter};
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'foundation:cache:flush', description: 'Flush manifest/result cache (file and Redis)')]
final class FoundationCacheFlushCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adapter = null;
        $redisUrl = getenv('REDIS_URL') ?: null;
        if ($redisUrl && class_exists('\Redis')) {
            $adapter = new RedisCacheAdapter($redisUrl);
        } else {
            $adapter = new FileCacheAdapter(getenv('OBJECT_FOUNDATION_CACHE_DIR') ?: 'var/cache/manifests');
        }
        $cache = new ManifestCache($adapter, (int)(getenv('OBJECT_FOUNDATION_CACHE_TTL') ?: 3600));
        $cache->clear();
        $output->writeln('<info>Cache cleared.</info>');
        return Command::SUCCESS;
    }
}
