<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$src = "$root/src";

function find_violations(string $pattern, string $exclude): array {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($GLOBALS['src']));
    $violations = [];
    foreach ($rii as $file) {
        if ($file->isDir() || !str_ends_with($file->getFilename(), '.php')) continue;
        $path = $file->getPathname();
        if (str_contains($path, $exclude)) continue;
        $contents = file_get_contents($path);
        if (preg_match($pattern, $contents)) $violations[] = $path;
    }
    return $violations;
}

$core = find_violations('/use\s+(Symfony\\\\|Illuminate\\\\)/', 'src/Bridge/');
$cmds = find_violations('/(AsCommand\s*\(|extends\s+Command\b)/', 'src/Bridge/Symfony/Command');

if ($core || $cmds) {
    echo "❌ Atomicity violations detected:\n";
    foreach (array_merge($core, $cmds) as $f) echo " - $f\n";
    exit(1);
}

echo "✅ Atomicity OK for src/ and Bridge/\n";
