<?php

namespace App\Tests\Functionnal\\Repository;

use App\Repository\DexAvailabilityRepository;
use App\Tests\resources\functionnal\CounterTrait\CountDexAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DexAvailabilityRepositoryTest extends KernelTestCase
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

        /** @var DexAvailabilityRepository $repo */
        $repo = static::getContainer()->get(DexAvailabilityRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getDexAvailabilityCount());
    }
}
