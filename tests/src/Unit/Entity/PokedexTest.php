<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Pokedex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Pokedex::class)]
final class PokedexTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new Pokedex();

        $this->assertNull($entity->getIdentifier());
    }
}
