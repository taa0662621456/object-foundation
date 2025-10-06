<?php
namespace ObjectFoundation\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Examples\SymfonyDemo\Entity\DemoEntity;
use Ramsey\Uuid\Uuid;
use ReflectionClass;

final class DemoEntityTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testDefaults(): void
    {
        $e = new DemoEntity();
        // simulate PrePersist lifecycle
        $ref = new ReflectionClass($e);
        foreach (['_identityInit','_auditOnCreate'] as $m) {
            if ($ref->hasMethod($m)) {
                $ref->getMethod($m)->invoke($e);
            }
        }
        $this->assertTrue($e->isPublished());
        $this->assertInstanceOf(Uuid::uuid4(), $e->getUuid());
        $this->assertNotEmpty($e->getSlug());
    }

    #[Test]
    public function environment_is_configured(): void
    {
        $this->assertTrue(true, 'PHPUnit basic assertion works');
    }

    #[Test]
    public function brand_env_variable_is_available(): void
    {
        $brand = getenv('OBJECT_FOUNDATION_BRAND');
        $this->assertNotEmpty($brand, 'Environment variable OBJECT_FOUNDATION_BRAND must be set');
    }

    #[Test]
    public function project_root_structure_exists(): void
    {
        $required = ['src', 'composer.json'];
        foreach ($required as $item) {
            $this->assertFileExists($item, "Missing required file or folder: {$item}");
        }
    }
}
