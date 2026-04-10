<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\DTO\ElectionVote;
use App\Service\ElectionUpdateEloService;
use App\Tests\Common\Traits\GetterTrait\GetTrainerPokemonEloTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(ElectionUpdateEloService::class)]
class ElectionUpdateEloServiceTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetTrainerPokemonEloTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testVote(): void
    {
        /** @var ElectionUpdateEloService $service */
        $service = static::getContainer()->get(ElectionUpdateEloService::class);

        $results = $service->update(
            new ElectionVote([
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'demo',
                'election_slug' => '',
                'winners_slugs' => ['bulbasaur'],
                'losers_slugs' => ['ivysaur', 'venusaur'],
            ])
        );

        $this->assertCount(2, $results);
        $this->assertSame(['winners', 'losers'], array_keys($results));

        $this->assertSame('bulbasaur', $results['winners'][0]->getPokemonSlug());
        $this->assertSame(1027, $results['winners'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 1027,
                'view_count' => 1,
                'win_count' => 1,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 'bulbasaur'),
        );

        $this->assertSame('ivysaur', $results['losers'][0]->getPokemonSlug());
        $this->assertSame(1003, $results['losers'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 1003,
                'view_count' => 1,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 'ivysaur'),
        );

        $this->assertSame('venusaur', $results['losers'][1]->getPokemonSlug());
        $this->assertSame(1013, $results['losers'][1]->getElo());
        $this->assertSame(
            [
                'elo' => 1013,
                'view_count' => 1,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', '', 'venusaur'),
        );
    }

    public function testVoteBis(): void
    {
        /** @var ElectionUpdateEloService $service */
        $service = static::getContainer()->get(ElectionUpdateEloService::class);

        $results = $service->update(
            new ElectionVote([
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'demo',
                'election_slug' => 'favorite',
                'winners_slugs' => ['bulbasaur'],
                'losers_slugs' => ['ivysaur', 'venusaur', 'venusaur-f'],
            ])
        );

        $this->assertCount(2, $results);
        $this->assertSame(['winners', 'losers'], array_keys($results));

        $this->assertSame('bulbasaur', $results['winners'][0]->getPokemonSlug());
        $this->assertSame(1016, $results['winners'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 1016,
                'view_count' => 1,
                'win_count' => 1,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', 'favorite', 'bulbasaur'),
        );

        $this->assertSame('ivysaur', $results['losers'][0]->getPokemonSlug());
        $this->assertSame(984, $results['losers'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 984,
                'view_count' => 1,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', 'favorite', 'ivysaur'),
        );

        $this->assertSame('venusaur', $results['losers'][1]->getPokemonSlug());
        $this->assertSame(984, $results['losers'][1]->getElo());
        $this->assertSame(
            [
                'elo' => 984,
                'view_count' => 1,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', 'favorite', 'venusaur'),
        );

        $this->assertSame('venusaur-f', $results['losers'][2]->getPokemonSlug());
        $this->assertSame(984, $results['losers'][2]->getElo());
        $this->assertSame(
            [
                'elo' => 984,
                'view_count' => 1,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'demo', 'favorite', 'venusaur-f'),
        );
    }

    public function testVoteTer(): void
    {
        /** @var ElectionUpdateEloService $service */
        $service = static::getContainer()->get(ElectionUpdateEloService::class);

        $results = $service->update(
            new ElectionVote([
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'redgreenblueyellow',
                'election_slug' => 'affinee',
                'winners_slugs' => ['venusaur'],
                'losers_slugs' => ['butterfree'],
            ])
        );

        $this->assertCount(2, $results);
        $this->assertSame(['winners', 'losers'], array_keys($results));

        $this->assertSame('venusaur', $results['winners'][0]->getPokemonSlug());
        $this->assertSame(1045, $results['winners'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 1045,
                'view_count' => 3,
                'win_count' => 3,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'venusaur'),
        );

        $this->assertSame('butterfree', $results['losers'][0]->getPokemonSlug());
        $this->assertSame(955, $results['losers'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 955,
                'view_count' => 3,
                'win_count' => 0,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'butterfree'),
        );
    }

    public function testVoteAllLosers(): void
    {
        /** @var ElectionUpdateEloService $service */
        $service = static::getContainer()->get(ElectionUpdateEloService::class);

        $results = $service->update(
            new ElectionVote([
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'redgreenblueyellow',
                'election_slug' => 'affinee',
                'winners_slugs' => [],
                'losers_slugs' => ['ivysaur', 'venusaur'],
            ])
        );

        $this->assertCount(2, $results);
        $this->assertSame(['winners', 'losers'], array_keys($results));

        $this->assertEmpty($results['winners']);

        $this->assertSame('ivysaur', $results['losers'][0]->getPokemonSlug());
        $this->assertSame(999, $results['losers'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 999,
                'view_count' => 2,
                'win_count' => 1,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'ivysaur'),
        );

        $this->assertSame('venusaur', $results['losers'][1]->getPokemonSlug());
        $this->assertSame(1015, $results['losers'][1]->getElo());
        $this->assertSame(
            [
                'elo' => 1015,
                'view_count' => 3,
                'win_count' => 2,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'venusaur'),
        );
    }

    public function testVoteAllWinners(): void
    {
        /** @var ElectionUpdateEloService $service */
        $service = static::getContainer()->get(ElectionUpdateEloService::class);

        $results = $service->update(
            new ElectionVote([
                'trainer_external_id' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                'dex_slug' => 'redgreenblueyellow',
                'election_slug' => 'affinee',
                'winners_slugs' => ['ivysaur', 'venusaur'],
                'losers_slugs' => [],
            ])
        );

        $this->assertCount(2, $results);
        $this->assertSame(['winners', 'losers'], array_keys($results));

        $this->assertSame('ivysaur', $results['winners'][0]->getPokemonSlug());
        $this->assertSame(1031, $results['winners'][0]->getElo());
        $this->assertSame(
            [
                'elo' => 1031,
                'view_count' => 2,
                'win_count' => 2,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'ivysaur'),
        );

        $this->assertSame('venusaur', $results['winners'][1]->getPokemonSlug());
        $this->assertSame(1047, $results['winners'][1]->getElo());
        $this->assertSame(
            [
                'elo' => 1047,
                'view_count' => 3,
                'win_count' => 3,
            ],
            $this->getEloAndCounts('7b52009b64fd0a2a49e6d8a939753077792b0554', 'redgreenblueyellow', 'affinee', 'venusaur'),
        );

        $this->assertEmpty($results['losers']);
    }
}
