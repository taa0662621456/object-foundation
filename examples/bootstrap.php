<?php
require __DIR__ . '/../vendor/autoload.php';

use ObjectFoundation\SDK\FoundationSDK;
FoundationSDK::autoDetect()->boot();

echo "Object Foundation SDK booted.\n";