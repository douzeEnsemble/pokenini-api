<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\CategoryFormsRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryFormsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var CategoryFormsRepository $repo */
        $repo = static::getContainer()->get(CategoryFormsRepository::class);

        $list = $repo->getAll();

        $this->assertCount(3, $list);
    }
}
