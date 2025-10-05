<?php
declare(strict_types=1);

namespace ObjectFoundation\Tests;

use PHPUnit\Framework\TestCase;
use ObjectFoundation\Kernel;
use ObjectFoundation\Http\Request;

final class KernelTest extends TestCase
{
    public function testKernelBoots(): void
    {
        $req = new Request('GET', '/health', [], [], [], '127.0.0.1', microtime(true));
        $kernel = new Kernel();
        $resp = $kernel->handle($req);
        $this->assertNotNull($resp);
        $this->assertIsString($resp->body);
    }
}
