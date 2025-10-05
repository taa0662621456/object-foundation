<?php
namespace ObjectFoundation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Examples\SymfonyDemo\Entity\DemoEntity;
use ReflectionClass;
use Symfony\Component\Uid\Uuid;

final class DemoEntityTest extends TestCase
{
    public function testDefaults(): void
    {
        $e = new DemoEntity();
        // simulate PrePersist lifecycle
        $ref = new ReflectionClass($e);
        foreach (['_identityInit','_auditOnCreate'] as $m) {
            if ($ref->hasMethod($m)) {
                $ref->getMethod($m)->setAccessible(true);
                $ref->getMethod($m)->invoke($e);
            }
        }
        $this->assertTrue($e->isPublished());
        $this->assertInstanceOf(Uuid::class, $e->getUuid());
        $this->assertNotEmpty($e->getSlug());
    }
}
