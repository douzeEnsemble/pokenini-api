<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Pokemon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Pokemon::class)]
class PokemonTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new Pokemon();

        $this->assertNull($entity->getIdentifier());
    }

    public function testConvertToString(): void
    {
        $entity = new Pokemon();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
