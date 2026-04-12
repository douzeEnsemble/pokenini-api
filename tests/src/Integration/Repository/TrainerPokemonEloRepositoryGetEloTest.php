<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerPokemonEloRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloRepository::class)]
final class TrainerPokemonEloRepositoryGetEloTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[DataProvider('providerGetElo')]
    public function testGetElo(string $pokemonSlug, ?int $expectedElo): void
    {
        /** @var TrainerPokemonEloRepository $repo */
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $this->assertSame(
            $expectedElo,
            $repo->getElo('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', $pokemonSlug),
        );
    }

    /**
     * @return array<string, array{
     *  pokemonSlug: string,
     *  expectedElo: null|int,
     * }>
     */
    public static function providerGetElo(): array
    {
        return [
            'bulbasaur' => [
                'pokemonSlug' => 'bulbasaur',
                'expectedElo' => 1010,
            ],
            'ivysaur' => [
                'pokemonSlug' => 'ivysaur',
                'expectedElo' => 1020,
            ],
            'venusaur' => [
                'pokemonSlug' => 'venusaur',
                'expectedElo' => 1030,
            ],
            'venusaur-f' => [
                'pokemonSlug' => 'venusaur-f',
                'expectedElo' => 1040,
            ],
            'venusaur-mega' => [
                'pokemonSlug' => 'venusaur-mega',
                'expectedElo' => 1050,
            ],
            'venusaur-gmax' => [
                'pokemonSlug' => 'venusaur-gmax',
                'expectedElo' => 1060,
            ],
            'pikachu' => [
                'pokemonSlug' => 'pikachu',
                'expectedElo' => null,
            ],
        ];
    }
}
