<?php

namespace App\Tests\functionnal\Repository;

use App\Repository\GameBundleRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameBundleRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var GameBundleRepository $repo */
        $repo = static::getContainer()->get(GameBundleRepository::class);

        $list = $repo->getAll();

        $this->assertCount(16, $list);
    }
}
