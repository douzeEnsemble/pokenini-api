<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\SpecialFormsRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SpecialFormsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var SpecialFormsRepository $repo */
        $repo = static::getContainer()->get(SpecialFormsRepository::class);

        $list = $repo->getAll();

        $this->assertCount(3, $list);
    }
}
