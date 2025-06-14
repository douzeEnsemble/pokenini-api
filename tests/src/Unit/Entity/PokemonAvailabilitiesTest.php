<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\PokemonAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilities::class)]
class PokemonAvailabilitiesTest extends TestCase
{
    public function testGetIdentifierDefault(): void
    {
        $entity = new PokemonAvailabilities();

        $this->assertNull($entity->getIdentifier());
    }
}
