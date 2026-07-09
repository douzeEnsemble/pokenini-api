<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\PokedexRepository;
use App\Repository\Trait\FiltersTrait;
use App\Tests\Common\Traits\GetterTrait\GetPokedexTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(PokedexRepository::class)]
#[CoversTrait(FiltersTrait::class)]
final class PokedexRepositoryCatchStateCountTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use GetPokedexTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetCatchStatesCounts(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStatesCounts(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'redgreenblueyellow',
            AlbumFilters::createFromArray([]),
        );

        $this->assertEquals(
            [
                [
                    'count' => 4,
                    'slug' => 'no',
                    'name' => 'No',
                    'french_name' => 'Non',
                    'color' => '#e57373',
                ],
                [
                    'count' => 1,
                    'slug' => 'maybe',
                    'name' => 'Maybe',
                    'french_name' => 'Peut être',
                    'color' => 'blue',
                ],
                [
                    'count' => 2,
                    'slug' => 'maybenot',
                    'name' => 'Maybe not',
                    'french_name' => 'Peut être pas',
                    'color' => 'yellow',
                ],
                [
                    'count' => 0,
                    'slug' => 'yes',
                    'name' => 'Yes',
                    'french_name' => 'Oui',
                    'color' => '#66bb6a',
                ],
            ],
            $counts
        );
    }

    public function testGetBatchedCatchStatesCounts(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getBatchedCatchStatesCounts('7b52009b64fd0a2a49e6d8a939753077792b0554');

        $byDexSlugAndCatchState = [];
        foreach ($counts as $row) {
            $byDexSlugAndCatchState[$row['dex_slug']][$row['slug']] = $row['count'];
        }

        $this->assertEquals(
            ['no' => 9, 'maybe' => 3, 'maybenot' => 3, 'yes' => 7],
            $byDexSlugAndCatchState['home']
        );
        $this->assertEquals(
            ['no' => 11, 'maybe' => 0, 'maybenot' => 0, 'yes' => 0],
            $byDexSlugAndCatchState['home_shiny']
        );
        $this->assertEquals(
            ['no' => 8, 'maybe' => 0, 'maybenot' => 0, 'yes' => 1],
            $byDexSlugAndCatchState['goldsilvercrystal']
        );
    }

    /**
     * @param array<string, array<int, string>> $filters
     * @param array<int, int>                   $expectedCounts
     */
    #[DataProvider('providerGetCatchStatesCountsFilters')]
    public function testGetCatchStatesCountsFilters(
        array $filters,
        array $expectedCounts
    ): void {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStatesCounts(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray($filters),
        );

        $this->assertEquals($expectedCounts[0], $counts[0]['count']);
        $this->assertEquals($expectedCounts[1], $counts[1]['count']);
        $this->assertEquals($expectedCounts[2], $counts[2]['count']);
        $this->assertEquals($expectedCounts[3], $counts[3]['count']);
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedCounts: array<int, int>,
     * }>
     */
    public static function providerGetCatchStatesCountsFilters(): array
    {
        return array_merge(
            PokedexRepositoryCatchStateCountData::providerGetCatchStatesCountsTypesFilters(),
            PokedexRepositoryCatchStateCountData::providerGetCatchStatesCountsFormsFilters(),
            PokedexRepositoryCatchStateCountData::providerGetCatchStatesCountsCatchStatesFilters(),
            PokedexRepositoryCatchStateCountData::providerGetCatchStatesCountsOriginalGamesFilters(),
            PokedexRepositoryCatchStateCountData::providerGetCatchStatesCountGamesBundlesFilters(),
            PokedexRepositoryCatchStateCountData::providerGetCatchStatesCountsFamiliesFilters(),
            PokedexRepositoryCatchStateCountData::providerGetCatchStatesCountCollectionsFilters(),
        );
    }

    public function testGetCatchStateCountsDefinedByTrainer(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStateCountsDefinedByTrainer();

        $this->assertEquals(
            [
                [
                    'nb' => 28,
                    'trainer' => '7b52009b64fd0a2a49e6d8a939753077792b0554',
                ],
                [
                    'nb' => 28,
                    'trainer' => 'd033e22ae348aeb5660fc2140aec35850c4da997',
                ],
                [
                    'nb' => 16,
                    'trainer' => 'e9b14f010c91507ddbdd32bd28911ba8a9f3d30f',
                ],
                [
                    'nb' => 13,
                    'trainer' => '297e1479cf75d300a89a5b6ec208fd979209878b',
                ],
                [
                    'nb' => 3,
                    'trainer' => 'bd307a3ec329e10a2cff8fb87480823da114f8f4',
                ],
            ],
            $counts
        );
    }

    public function testGetCatchStateUsage(): void
    {
        $repo = self::getContainer()->get(PokedexRepository::class);

        $counts = $repo->getCatchStateUsage();

        $this->assertEquals(
            [
                [
                    'nb' => 28,
                    'slug' => 'no',
                    'name' => 'No',
                    'french_name' => 'Non',
                    'color' => '#e57373',
                ],
                [
                    'nb' => 14,
                    'slug' => 'maybe',
                    'name' => 'Maybe',
                    'french_name' => 'Peut être',
                    'color' => 'blue',
                ],
                [
                    'nb' => 13,
                    'slug' => 'maybenot',
                    'name' => 'Maybe not',
                    'french_name' => 'Peut être pas',
                    'color' => 'yellow',
                ],
                [
                    'nb' => 33,
                    'slug' => 'yes',
                    'name' => 'Yes',
                    'french_name' => 'Oui',
                    'color' => '#66bb6a',
                ],
            ],
            $counts
        );
    }
}
