<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\PokemonColorSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(PokemonColorSet::class)]
final class PokemonColorSetTest extends TestCase
{
    #[Test]
    public function getIdentifierDefault(): void
    {
        $entity = new PokemonColorSet();

        $this->assertNull($entity->getIdentifier());
    }
}
