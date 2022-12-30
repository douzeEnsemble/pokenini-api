<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\RegionRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegionRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var RegionRepository $repo */
        $repo = static::getContainer()->get(RegionRepository::class);

        $list = $repo->getAllNames();

        $this->assertEquals(
            [
                'Kanto',
                'Johto',
                'Hoenn',
                'Sinnoh',
                'Unova',
                'Kalos',
                'Alola',
                'Galar',
                'Hisui',
                'Paldea',
            ],
            $list
        );
    }
}
