<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\GamesShiniesAvailabilitiesRepository;
use App\Tests\Common\Traits\CounterTrait\CountGameShinyAvailabilityTrait;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GamesShiniesAvailabilitiesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;
    use CountGameShinyAvailabilityTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testRemoveAll(): void
    {
        $this->assertGreaterThan(0, $this->getGameShinyAvailabilityCount());

        /** @var GamesShiniesAvailabilitiesRepository $repo */
        $repo = static::getContainer()->get(GamesShiniesAvailabilitiesRepository::class);
        $repo->removeAll();

        $this->assertEquals(0, $this->getGameShinyAvailabilityCount());
    }
}
