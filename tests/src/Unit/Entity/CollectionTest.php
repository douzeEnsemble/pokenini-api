<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new Collection();

        $this->assertNull($entity->getIdentifier());
    }

    public function testConvertToString(): void
    {
        $entity = new Collection();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
