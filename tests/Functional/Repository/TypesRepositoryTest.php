<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\TypesRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TypesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var TypesRepository $repo */
        $repo = static::getContainer()->get(TypesRepository::class);

        $list = $repo->getAll();

        $this->assertCount(18, $list);
    }
}
