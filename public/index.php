<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ObjectFoundation\Kernel;
use ObjectFoundation\Http\Request;

$kernel = new Kernel();
$response = $kernel->handle(Request::fromGlobals());
$response->send();
