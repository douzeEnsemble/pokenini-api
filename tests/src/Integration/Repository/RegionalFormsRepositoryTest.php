<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\RegionalFormsRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(RegionalFormsRepository::class)]
class RegionalFormsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
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
