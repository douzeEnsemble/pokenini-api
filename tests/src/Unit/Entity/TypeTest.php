<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Type::class)]
final class TypeTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new Type();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new Type();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
