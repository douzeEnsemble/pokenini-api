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
                ['source_name' => 'Bulbapedia', 'source_url' => 'https://bulbapedia.bulbagarden.net'],
                ['source_name' => 'PokemonDB', 'source_url' => 'https://pokemondb.net/sprites/bulbasaur'],
                ['source_name' => 'PokéSprite', 'source_url' => 'https://github.com/msikma/pokesprite'],
                ['source_name' => 'Serebii', 'source_url' => 'https://serebii.net'],
            ],
            $result,
        );
    }
}
