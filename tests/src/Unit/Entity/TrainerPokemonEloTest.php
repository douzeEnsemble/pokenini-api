<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Dex;
use App\Entity\Pokemon;
use App\Entity\TrainerPokemonElo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonElo::class)]
final class TrainerPokemonEloTest extends TestCase
{
    #[Test]
    public function constructorAndGetters(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $dex = new Dex();
        $dex->slug = 'demo';

        $entity = new TrainerPokemonElo('121212', $pokemon, $dex);

        $this->assertSame('121212', $entity->getTrainerExternalId());
        $this->assertSame($pokemon, $entity->getPokemon());
        $this->assertSame(0, $entity->elo);
        $this->assertSame($dex, $entity->getDex());
        $this->assertSame('', $entity->getElectionSlug());
    }

    #[Test]
    public function constructorAndGettersWithElectionSlug(): void
    {
        $pokemon = new Pokemon();
        $pokemon->slug = 'Douze';

        $dex = new Dex();
        $dex->slug = 'demo';

        $entity = new TrainerPokemonElo('121212', $pokemon, $dex, 'pref');

        $this->assertSame('121212', $entity->getTrainerExternalId());
        $this->assertSame($pokemon, $entity->getPokemon());
        $this->assertSame(0, $entity->elo);
        $this->assertSame($dex, $entity->getDex());
        $this->assertSame('pref', $entity->getElectionSlug());
    }
}
