<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\DexCategory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(DexCategory::class)]
final class DexCategoryTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new DexCategory();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new DexCategory();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
