<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\SpecialFormsRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(SpecialFormsRepository::class)]
final class SpecialFormsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var SpecialFormsRepository $repo */
        $repo = self::getContainer()->get(SpecialFormsRepository::class);

        $list = $repo->getAll();

        $this->assertCount(4, $list);
    }
}
