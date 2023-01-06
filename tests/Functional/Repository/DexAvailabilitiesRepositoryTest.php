<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

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

        $totalCount = $repo->getTotal('redgreenblueyellow');

        $this->assertEquals(7, $totalCount);
    }
}
