<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Repository\GameRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameRepositoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
    }

    public function testGetAll(): void
    {
        /** @var GameRepository $repo */
        $repo = static::getContainer()->get(GameRepository::class);

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
