<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\PokemonAvailabilities;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonAvailabilities::class)]
final class PokemonAvailabilitiesTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new PokemonAvailabilities();

        $this->assertNull($entity->getIdentifier());
    }
}
