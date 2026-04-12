<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Repository\GamesRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
#[CoversClass(GamesRepository::class)]
final class GamesRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    #[\Override]
    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var GamesRepository $repo */
        $repo = self::getContainer()->get(GamesRepository::class);

        $list = $repo->getAllSlugs();

        $this->assertEquals(
            [
                'red',
                'green',
                'blue',
                'yellow',
                'gold',
                'silver',
                'crystal',
                'ruby',
                'sapphire',
                'firered',
                'leafgreen',
                'emerald',
                'diamond',
                'pearl',
                'platinium',
                'heartgold',
                'soulsilver',
                'black',
                'white',
                'black2',
                'white2',
                'x',
                'y',
                'omegaruby',
                'alphasapphire',
                'sun',
                'moon',
                'ultrasun',
                'ultramoon',
                'letsgopikachu',
                'letsgoeevee',
                'sword',
                'shield',
                'brilliantdiamond',
                'shiningpearl',
                'pokemonlegendsarceus',
                'scarlet',
                'violet',
            ],
            $list
        );
    }
}
