<?php

declare(strict_types=1);

namespace App\Tests\Unit\Updater;

use App\Service\SpreadsheetService;
use App\Updater\PokemonsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(PokemonsUpdater::class)]
final class PokemonsUpdaterTest extends TestCase
{
    public function testTransformRecordMapsFieldsCorrectly(): void
    {
        $spreadsheetService = $this->createStub(SpreadsheetService::class);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $logger = $this->createStub(LoggerInterface::class);

        $updater = new PokemonsUpdaterTestDouble($spreadsheetService, $entityManager, $logger, 'test');

        $result = $updater->exposeTransformRecord($this->getRecord());

        $this->assertSame('Pikachu', $result['name']);
        $this->assertSame('Pika', $result['simplifiedName']);
        $this->assertSame('', $result['formsLabel']);
        $this->assertSame('Pikachu', $result['frenchName']);
        $this->assertSame('Pika', $result['simplifiedFrenchName']);
        $this->assertSame('', $result['formsFrenchLabel']);
        $this->assertSame(25, $result['nationalDexNumber']);
        $this->assertSame('pikachu', $result['family']);
        $this->assertSame('1', $result['familyOrder']);
        $this->assertTrue($result['bankable']);
        $this->assertFalse($result['bankableish']);
        $this->assertSame('scarlet-violet', $result['originalGameBundle']);
        $this->assertSame('', $result['variantForm']);
        $this->assertSame('', $result['regionalForm']);
        $this->assertSame('', $result['specialForm']);
        $this->assertSame('', $result['categoryForm']);
        $this->assertSame('electric', $result['primaryType']);
        $this->assertSame('', $result['secondaryType']);
        $this->assertSame('0025.png', $result['iconName']);
        $this->assertSame('pikachu', $result['slug']);
        $this->assertSame('PokéSprite', $result['smallRegularCreditName']);
        $this->assertSame('https://github.com/msikma/pokesprite', $result['smallRegularCreditUrl']);
        $this->assertSame('PokéSprite Shiny', $result['smallShinyCreditName']);
        $this->assertSame('https://github.com/msikma/pokesprite/shiny', $result['smallShinyCreditUrl']);
        $this->assertSame('PokemonDB', $result['bigRegularCreditName']);
        $this->assertSame('https://pokemondb.net/sprites/pikachu', $result['bigRegularCreditUrl']);
        $this->assertSame('PokemonDB Shiny', $result['bigShinyCreditName']);
        $this->assertSame('https://pokemondb.net/sprites/pikachu-shiny', $result['bigShinyCreditUrl']);
    }

    /** @return string[] */
    private function getRecord(): array
    {
        return [
            'Bankable' => 'true',
            'Bankable-ish' => 'false',
            'Breeedable Form' => '',
            '#Origin' => '',
            '#Games First Appears On' => 'scarlet-violet',
            '#Form variant' => '',
            '#Regional form' => '',
            '#Special form' => '',
            '#Category form' => '',
            '#Family' => 'pikachu',
            'Family order' => '1',
            'Slug' => 'pikachu',
            'Pokémon Nom Complet' => 'Pikachu',
            'Pokémon Nom simplifié' => 'Pika',
            'Forme' => '',
            'Pokémon Nom Complet Fr' => 'Pikachu',
            'Pokémon Nom simplifié Fr' => 'Pika',
            'Forme Fr' => '',
            'Dex' => '25',
            'Sprites' => '',
            'Shiny Sprites' => '',
            'Icon' => '0025.png',
            'Sprites url' => 'https://pokemondb.net/sprites/pikachu',
            'Shiny Sprites url' => 'https://pokemondb.net/sprites/pikachu-shiny',
            '#Type 1' => 'electric',
            '#Type 2' => '',
            'Species number' => '25',
            'MBCMechachu sprites index' => '',
            'PokemonDB icon name' => '',
            'PokemonDB icon dex' => '',
            'generic-slug' => '',
            '#Groups' => '',
            'Icon Source' => 'PokéSprite',
            'Icon Source Url' => 'https://github.com/msikma/pokesprite',
            'Shiny Icon Source' => 'PokéSprite Shiny',
            'Shiny Icon Source Url' => 'https://github.com/msikma/pokesprite/shiny',
            'Sprites Source' => 'PokemonDB',
            'Shiny Sprites Source' => 'PokemonDB Shiny',
        ];
    }
}

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class PokemonsUpdaterTestDouble extends PokemonsUpdater
{
    /**
     * @param string[] $record
     *
     * @return bool[]|int[]|string[]
     */
    public function exposeTransformRecord(array $record): array
    {
        return $this->transformRecord($record);
    }
}
