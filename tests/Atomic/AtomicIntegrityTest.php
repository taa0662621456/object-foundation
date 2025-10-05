<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AtomicIntegrityTest extends TestCase
{
    public function testCoreHasNoForbiddenFrameworkDeps(): void
    {
        $base = __DIR__ . '/../../src';
        $forbid = ['Symfony\\Component\\', 'Illuminate\\', 'Laravel\\'];
        $allow = ['ObjectFoundation\\', 'Doctrine\\', 'Psr\\', 'Symfony\\Contracts\\'];

        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
        $violations = [];

        foreach ($rii as $file) {
            if ($file->isDir()) continue;
            $path = str_replace('\\', '/', $file->getPathname());
            if (!str_ends_with($path, '.php')) continue;
            // skip bridges and sdk
            if (str_contains($path, '/Bridge/') || str_contains($path, '/SDK/')) continue;

            $code = file_get_contents($path) ?: '';
            if (preg_match_all('/\buse\s+([A-Za-z0-9_\\\\]+)\s*;/m', $code, $m)) {
                foreach ($m[1] as $ns) {
                    if (self::isForbidden($ns, $forbid, $allow)) $violations[] = [$path, $ns, 'use'];
                }
            }
            if (preg_match_all('/new\s+\\?([A-Za-z0-9_\\\\]+)\s*\(/m', $code, $m2)) {
                foreach ($m2[1] as $ns) {
                    if (self::isForbidden($ns, $forbid, $allow)) $violations[] = [$path, $ns, 'new'];
                }
            }
        }

        $msg = '';
        foreach ($violations as [$file, $ns, $kind]) $msg .= "{$file}: {$kind} {$ns}
";
        $this->assertEmpty($violations, "Atomic integrity violations:\n".$msg);
    }

    private static function isForbidden(string $ns, array $forbid, array $allow): bool
    {
        foreach ($allow as $ok) if (str_starts_with($ns, $ok)) return false;
        foreach ($forbid as $bad) if (str_starts_with($ns, $bad)) return true;
        return false;
    }
}