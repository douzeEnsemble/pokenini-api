<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Type::class)]
class TypeTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new Type();

        $this->assertNull($entity->getIdentifier());
    }

    public function testConvertToString(): void
    {
        $entity = new Type();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
