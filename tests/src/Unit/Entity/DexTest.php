<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Dex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Dex::class)]
final class DexTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new Dex();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new Dex();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
