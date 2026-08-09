<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\DexAvailabilitiesRepository;
use App\Repository\Trait\FiltersTrait;
use App\Tests\Common\Traits\CounterTrait\CountDexAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(DexAvailabilitiesRepository::class)]
#[CoversTrait(FiltersTrait::class)]
final class DexAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountDexAvailabilityTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function removeAll(): void
    {
        $this->assertGreaterThan(0, $this->getDexAvailabilityCount());

        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getDexAvailabilityCount());
    }

    #[Test]
    public function getTotal(): void
    {
        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([])
        );

        $this->assertEquals(22, $totalCount);
    }

    #[Test]
    public function getTotalDifferentTrainer(): void
    {
        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'home',
            AlbumFilters::createFromArray([])
        );

        $this->assertEquals(22, $totalCount);
    }

    /**
     * @param string[][] $filters
     */
    #[Test]
    #[DataProvider('providerGetTotalFilters')]
    public function getTotalFilters(array $filters, int $expectedTotalCount): void
    {
        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray($filters)
        );

        $this->assertEquals($expectedTotalCount, $totalCount);
    }

    /**
     * @return array<string, array{
     *  filters: array<string, array<int, string>>,
     *  expectedTotalCount: int,
     * }>
     */
    public static function providerGetTotalFilters(): array
    {
        return array_merge(
            DexAvailabilitiesRepositoryData::providerGetTotalTypesFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalFormsFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalCatchStatesFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalGamesFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalCollectionsFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalFamiliesFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalEmptyFilters(),
        );
    }

    #[Test]
    public function getBatchedTotal(): void
    {
        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);

        $rows = $repo->getBatchedTotal('7b52009b64fd0a2a49e6d8a939753077792b0554');

        $byDexSlug = [];
        foreach ($rows as $row) {
            $byDexSlug[$row['dex_slug']] = $row['total'];
        }

        $this->assertSame(22, $byDexSlug['home']);
        $this->assertSame(9, $byDexSlug['goldsilvercrystal']);
        $this->assertSame(11, $byDexSlug['home_shiny']);
    }

    #[Test]
    public function getBatchedTotalDifferentTrainer(): void
    {
        $repo = self::getContainer()->get(DexAvailabilitiesRepository::class);

        $rows = $repo->getBatchedTotal('bd307a3ec329e10a2cff8fb87480823da114f8f4');

        $byDexSlug = [];
        foreach ($rows as $row) {
            $byDexSlug[$row['dex_slug']] = $row['total'];
        }

        $this->assertSame(22, $byDexSlug['home']);
    }
}
