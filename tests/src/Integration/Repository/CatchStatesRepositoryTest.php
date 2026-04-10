<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\CatchStatesRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(CatchStatesRepository::class)]
class CatchStatesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var CatchStatesRepository $repo */
        $repo = static::getContainer()->get(CatchStatesRepository::class);

        $list = $repo->getAll();

        $this->assertCount(4, $list);
    }
}
