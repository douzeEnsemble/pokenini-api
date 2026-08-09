<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Pokemon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Pokemon::class)]
final class PokemonTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new Pokemon();

        $this->assertNull($entity->getIdentifier());
    }

    #[Test]
    public function convertToString(): void
    {
        $entity = new Pokemon();
        $entity->name = 'Douze';

        $this->assertEquals('Douze', (string) $entity);
        $this->assertEquals('Douze', $entity->__toString());
    }
}
