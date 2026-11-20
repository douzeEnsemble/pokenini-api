<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Collection::class)]
final class CollectionTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new Collection();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new Collection();
        $entity->name = 'Douze';

        $this->assertSame('Douze', (string) $entity);
        $this->assertSame('Douze', $entity->__toString());
    }
}
