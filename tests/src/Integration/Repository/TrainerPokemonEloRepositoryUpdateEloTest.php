<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerPokemonEloRepository;
use App\Tests\Common\Traits\GetterTrait\GetTrainerPokemonEloTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloRepository::class)]
final class TrainerPokemonEloRepositoryUpdateEloTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetTrainerPokemonEloTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function updateElo(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $repo->updateElo(4556, '7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 'bulbasaur', true);

        $this->assertSame(
            [
                'elo' => 4556,
                'view_count' => 1,
                'win_count' => 1,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 'bulbasaur'),
        );
    }

    #[Test]
    public function updateNewElo(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $repo->updateElo(1212, '7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 'butterfree-gmax', false);

        $this->assertSame(
            [
                'elo' => 1212,
                'view_count' => 1,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 'butterfree-gmax'),
        );
    }

    #[Test]
    public function updateWinnerAgain(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $repo->updateElo(1048, '7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'venusaur', true);

        $this->assertSame(
            [
                'elo' => 1048,
                'view_count' => 3,
                'win_count' => 3,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'venusaur'),
        );
    }

    #[Test]
    public function updateLoserAgain(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $repo->updateElo(956, '7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'butterfree', false);

        $this->assertSame(
            [
                'elo' => 956,
                'view_count' => 3,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'butterfree'),
        );
    }
}
