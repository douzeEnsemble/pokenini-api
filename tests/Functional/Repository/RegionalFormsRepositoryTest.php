<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\RegionalFormsRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RegionalFormsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var RegionalFormsRepository $repo */
        $repo = static::getContainer()->get(RegionalFormsRepository::class);

        $list = $repo->getAll();

        $this->assertCount(3, $list);
    }
}
