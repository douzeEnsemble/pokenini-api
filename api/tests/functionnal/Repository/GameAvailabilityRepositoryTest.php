<?php

namespace App\Tests\Functionnal\\Repository;

use App\Repository\GameAvailabilityRepository;
use App\Tests\resources\functionnal\CounterTrait\CountGameAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameAvailabilityRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameAvailabilityCount());

        /** @var GameAvailabilityRepository $repo */
        $repo = static::getContainer()->get(GameAvailabilityRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameAvailabilityCount());
    }
}
