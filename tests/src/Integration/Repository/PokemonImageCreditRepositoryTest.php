<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\PokemonImageCreditRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokemonImageCreditRepository::class)]
final class PokemonImageCreditRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function findAllDistinctSourcesReturnsEachSourceOnceExcludingNullsAndOrderedByName(): void
    {
        $repo = self::getContainer()->get(PokemonImageCreditRepository::class);

        $result = $repo->findAllDistinctSources();

        self::assertCount(4, $result);
        self::assertSame(
            [
                ['source' => 'Bulbapedia - https://bulbapedia.bulbagarden.net'],
                ['source' => 'PokemonDB - https://pokemondb.net/sprites/bulbasaur'],
                ['source' => 'PokéSprite - https://github.com/msikma/pokesprite'],
                ['source' => 'Serebii - https://serebii.net'],
            ],
            $result,
        );
    }
}
