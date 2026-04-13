<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerPokemonEloRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloRepository::class)]
final class TrainerPokemonEloRepositoryTopNTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testTop5(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $list = $repo->getTopN('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 5);

        $this->assertCount(
            5,
            $list,
        );

        $this->assertAllKeysMatches(
            $list,
            'elo',
            [
                1060,
                1050,
                1040,
                1030,
                1020,
            ],
        );

        $this->assertAllKeysMatches(
            $list,
            'significance',
            [
                false,
                false,
                false,
                false,
                false,
            ],
        );
    }

    public function testTop5Home(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $list = $repo->getTopN('7b52009b64fd0a2a49e6d8a939753077792b0554', 'home', '', 5);

        $this->assertCount(
            0,
            $list,
        );
    }

    public function testTop5HomeFavorite(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $list = $repo->getTopN('7b52009b64fd0a2a49e6d8a939753077792b0554', 'home', 'favorite', 5);

        $this->assertCount(
            5,
            $list,
        );

        $this->assertAllKeysMatches(
            $list,
            'elo',
            [
                1000,
                1000,
                1000,
                1000,
                1000,
            ],
        );

        $this->assertAllKeysMatches(
            $list,
            'significance',
            [
                false,
                false,
                false,
                false,
                false,
            ],
        );
    }

    public function testTop10(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $list = $repo->getTopN('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 10);

        $this->assertCount(
            6,
            $list,
        );

        $this->assertAllKeysMatches(
            $list,
            'elo',
            [
                1060,
                1050,
                1040,
                1030,
                1020,
                1010,
            ],
        );

        $this->assertAllKeysMatches(
            $list,
            'significance',
            [
                false,
                false,
                false,
                false,
                false,
                false,
            ],
        );
    }

    public function testTopComplete(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $list = $repo->getTopN('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'favorite', 10);

        $this->assertCount(
            7,
            $list,
        );

        $this->assertAllKeysMatches(
            $list,
            'elo',
            [
                1016,
                984,
                984,
                984,
                984,
                984,
                984,
            ],
        );

        $this->assertAllKeysMatches(
            $list,
            'significance',
            [
                true,
                false,
                false,
                false,
                false,
                false,
                false,
            ],
        );
    }

    /**
     * @param float[][]|int[][]|string[][]  $list
     * @param bool[]|float[]|int[]|string[] $matches
     */
    private function assertAllKeysMatches(array $list, string $key, array $matches): void
    {
        $this->assertSame(
            $matches,
            array_map(
                fn ($value) => $value[$key],
                $list,
            ),
        );
    }
}
