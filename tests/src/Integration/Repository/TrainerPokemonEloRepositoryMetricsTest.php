<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TrainerPokemonEloRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TrainerPokemonEloRepository::class)]
final class TrainerPokemonEloRepositoryMetricsTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getMetrics(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $metrics = $repo->getMetrics('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '');

        $this->assertSame(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 15,
                'max_view_count' => 15,
                'dex_total_count' => 21,
            ],
            $metrics,
        );
    }

    #[Test]
    public function getMetricsBis(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $metrics = $repo->getMetrics('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee');

        $this->assertSame(
            [
                'view_count_sum' => 9,
                'win_count_sum' => 6,
                'view_count_max' => 3,
                'win_count_max' => 3,
                'under_max_view_count' => 1,
                'max_view_count' => 1,
                'dex_total_count' => 7,
            ],
            $metrics,
        );
    }

    #[Test]
    public function getMetricsNo(): void
    {
        $repo = self::getContainer()->get(TrainerPokemonEloRepository::class);

        $metrics = $repo->getMetrics('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'doesntexists');

        $this->assertSame(
            [
                'view_count_sum' => 0,
                'win_count_sum' => 0,
                'view_count_max' => 0,
                'win_count_max' => 0,
                'under_max_view_count' => 7,
                'max_view_count' => 0,
                'dex_total_count' => 7,
            ],
            $metrics,
        );
    }
}
