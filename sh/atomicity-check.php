#!/usr/bin/env php
<?php

/**
 * Atomicity Integrity Check
 * --------------------------
 * Ensures that the Object Foundation Core (`src/`) is free of framework dependencies.
 * Detects accidental Symfony, Doctrine, or Laravel imports in core domain code.
 *
 * Usage:
 *   php sh/atomicity-check.php [--exclude=Bridge,Infrastructure,Tests]
 */

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$srcDir  = $rootDir . '/src';

// CLI options
$options = getopt('', ['exclude:']);
$excluded = isset($options['exclude'])
    ? array_map('trim', explode(',', $options['exclude']))
    : ['Bridge', 'Infrastructure', 'Tests'];

// Patterns of forbidden dependencies
$forbidden = [
    'Symfony\\\\',
    'Doctrine\\\\',
    'Laravel\\\\',
    'Illuminate\\\\',
    'Psr\\\\Container\\\\',
    'Zend\\\\',
];

// Prepare iterator
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
);

$violations = [];

foreach ($iterator as $file) {
    /** @var SplFileInfo $file */
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());

    // Skip allowed folders
    foreach ($excluded as $ex) {
        if (str_contains($path, "src/$ex/")) {
            continue 2;
        }
    }

    $content = file_get_contents($path);

    foreach ($forbidden as $namespace) {
        if (str_contains($content, "use $namespace")) {
            $violations[] = $path;
            break;
        }
    }
}

// Reporting
if ($violations) {
    echo "\n\033[31m❌ Atomicity violations detected:\033[0m\n";
    foreach ($violations as $v) {
        echo " - $v\n";
    }
    echo "\nTotal: " . count($violations) . " file(s) violate atomicity rules.\n";
    exit(1);
}

echo "\n\033[32m✅ Core atomic integrity verified — no forbidden framework dependencies found.\033[0m\n";
exit(0);
