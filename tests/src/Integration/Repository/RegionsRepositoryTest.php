<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\RegionsRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(RegionsRepository::class)]
class RegionsRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var RegionsRepository $repo */
        $repo = static::getContainer()->get(RegionsRepository::class);

        $list = $repo->getAllSlugs();

        $this->assertEquals(
            [
                'kanto',
                'johto',
                'hoenn',
                'sinnoh',
                'unova',
                'kalos',
                'alola',
                'galar',
                'hisui',
                'paldea',
            ],
            $list
        );
    }
}
