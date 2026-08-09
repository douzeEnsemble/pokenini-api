<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\GameBundlesRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GameBundlesRepository::class)]
final class GameBundlesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    #[Test]
    public function getAll(): void
    {
        $repo = self::getContainer()->get(GameBundlesRepository::class);

        $list = $repo->getAll();

        $this->assertCount(19, $list);
    }
}
