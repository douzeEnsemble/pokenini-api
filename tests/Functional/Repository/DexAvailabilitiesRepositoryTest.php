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

        $totalCount = $repo->getTotal('home', AlbumFilters::createFromArray([]));

        $this->assertEquals(22, $totalCount);
    }

    public function testGetTotalPrimaryTypeFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'primaryTypes' => [
                    'grass',
                ],
            ])
        );

        $this->assertEquals(6, $totalCount);
    }

    public function testGetTotalSecondaryTypeFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'secondaryTypes' => [
                    'normal',
                ],
            ])
        );

        $this->assertEquals(3, $totalCount);
    }

    public function testGetTotalPrimaryAndSecondaryTypeFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'primaryTypes' => [
                    'bug',
                ],
                'secondaryTypes' => [
                    'flying',
                ],
            ])
        );

        $this->assertEquals(3, $totalCount);
    }

    public function testGetTotalAnyTypeFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'anyTypes' => [
                    'normal',
                ],
            ])
        );

        $this->assertEquals(7, $totalCount);
    }

    public function testGetTotalCategoryFormFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'categoryForms' => [
                    'starter',
                ],
            ])
        );

        $this->assertEquals(2, $totalCount);
    }

    public function testGetTotalRegionalFormFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'regionalForms' => [
                    'alolan',
                ],
            ])
        );

        $this->assertEquals(3, $totalCount);
    }

    public function testGetTotalSpecialFormFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'specialForms' => [
                    'gigantamax',
                ],
            ])
        );

        $this->assertEquals(2, $totalCount);
    }

    public function testGetTotalSpecialsFormFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'specialForms' => [
                    'gigantamax',
                    'mega',
                ],
            ])
        );

        $this->assertEquals(3, $totalCount);
    }

    public function testGetTotalVariantFormFilter(): void
    {
        /** @var DexAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilitiesRepository::class);

        $totalCount = $repo->getTotal(
            'home',
            AlbumFilters::createFromArray([
                'variantForms' => [
                    'gender',
                ],
            ])
        );

        $this->assertEquals(4, $totalCount);
    }
}
