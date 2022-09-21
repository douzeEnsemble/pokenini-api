<?php

declare(strict_types=1);

namespace App\Tests\Functionnal\Repository;

use App\Repository\CatchStateRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CatchStateRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var CatchStateRepository $repo */
        $repo = static::getContainer()->get(CatchStateRepository::class);

        $list = $repo->getAll();

        $this->assertCount(4, $list);
    }
}
