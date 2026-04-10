<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\TypesRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(TypesRepository::class)]
class TypesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
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
