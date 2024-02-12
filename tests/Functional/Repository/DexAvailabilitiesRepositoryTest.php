<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DTO\AlbumFilter\AlbumFilters;
use App\Repository\DexAvailabilitiesRepository;
use App\Tests\Common\Traits\CounterTrait\CountDexAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DexAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountDexAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getDexAvailabilityCount());

        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getDexAvailabilityCount());
    }

    public function testGetTotal(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray([])
        );

        $this->assertEquals(22, $totalCount);
    }

    public function testGetTotalDifferentTrainer(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'bd307a3ec329e10a2cff8fb87480823da114f8f4',
            'home',
            AlbumFilters::createFromArray([])
        );

        $this->assertEquals(22, $totalCount);
    }

    /**
     * @param string[][] $filters
     *
     * @dataProvider providerGetTotalFilters
     */
    public function testGetTotalFilters(array $filters, int $expectedTotalCount): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            '7b52009b64fd0a2a49e6d8a939753077792b0554',
            'home',
            AlbumFilters::createFromArray($filters)
        );

        $this->assertEquals($expectedTotalCount, $totalCount);
    }

    /**
     * @return string[][][][]|int[][]
     */
    public function providerGetTotalFilters(): array
    {
        return array_merge(
            DexAvailabilitiesRepositoryData::providerGetTotalTypesFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalFormsFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalCatchStatesFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalGamesFilters(),
            DexAvailabilitiesRepositoryData::providerGetTotalEmptyFilters(),
        );
    }
}
