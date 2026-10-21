<?php

declare(strict_types=1);

namespace App\Tests\Unit\Updater;

use App\Service\SpreadsheetService;
use App\Updater\PokemonsUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
#[CoversClass(PokemonsUpdater::class)]
final class PokemonsUpdaterTest extends TestCase
{
    #[Test]
    public function transformRecordMapsFieldsCorrectly(): void
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
        $this->assertSame('9', $result['originGeneration']);
        $this->assertSame('', $result['variantForm']);
        $this->assertSame('', $result['regionalForm']);
        $this->assertSame('', $result['specialForm']);
        $this->assertSame('', $result['categoryForm']);
        $this->assertSame('electric', $result['primaryType']);
        $this->assertSame('', $result['secondaryType']);
        $this->assertSame('0025.png', $result['iconName']);
        $this->assertSame('pikachu', $result['slug']);
        $this->assertSame('PokéSprite', $result['smallRegularCredit']);
        $this->assertSame('PokéSprite Shiny', $result['smallShinyCredit']);
        $this->assertSame('PokemonDB', $result['bigRegularCredit']);
        $this->assertSame('PokemonDB Shiny', $result['bigShinyCredit']);
        $this->assertSame('#F8D030;#000000 | #3080F0;#FFFFFF', $result['regularColorsCodes']);
        $this->assertSame('#F0B030;#000000 | #30F080;#FFFFFF', $result['shinyColorsCodes']);
        $this->assertSame('yellow;black | blue;white', $result['regularColorsNames']);
        $this->assertSame('orange;black | green;white', $result['shinyColorsNames']);
        $this->assertSame('jaune;noir | bleu;blanc', $result['regularColorsFrenchNames']);
        $this->assertSame('orange;noir | vert;blanc', $result['shinyColorsFrenchNames']);
    }

    /** @return string[] */
    private function getRecord(): array
    {
        return [
            'Bankable' => 'true',
            'Bankable-ish' => 'false',
            'Breeedable Form' => '',
            '#Origin' => '9',
            '#Games First Appears On' => 'scarlet-violet',
            '#Form variant' => '',
            '#Regional form' => '',
            '#Special form' => '',
            '#Category form' => '',
            '#Family' => 'pikachu',
            'Family order' => '1',
            'Slug' => 'pikachu',
            'Nom Complet' => 'Pikachu',
            'Nom simplifié' => 'Pika',
            'Forme' => '',
            'Nom Complet Fr' => 'Pikachu',
            'Nom simplifié Fr' => 'Pika',
            'Forme Fr' => '',
            'Dex' => '25',
            'Small Regular' => '',
            'Small Shiny' => '',
            'Big Regular' => '',
            'Big Shiny' => '',
            'Icon' => '0025.png',
            'Small Regular Url' => 'https://pokemondb.net/sprites/pikachu',
            'Small Shiny Url' => 'https://pokemondb.net/sprites/pikachu-shiny',
            'Big Regular Url' => '',
            'Big Shiny Url' => '',
            '#Type 1' => 'electric',
            '#Type 2' => '',
            'Species number' => '25',
            'MBCMechachu sprites index' => '',
            'PokemonDB icon name' => '',
            'PokemonDB icon dex' => '',
            'generic-slug' => '',
            '#Groups' => '',
            'Small Regular Credit' => 'PokéSprite',
            'Small Shiny Credit' => 'PokéSprite Shiny',
            'Big Regular Credit' => 'PokemonDB',
            'Big Shiny Credit' => 'PokemonDB Shiny',
            'Regular colours codes' => '#F8D030;#000000 | #3080F0;#FFFFFF',
            'Shiny coulours codes' => '#F0B030;#000000 | #30F080;#FFFFFF',
            'Regular colours names' => 'yellow;black | blue;white',
            'Shiny coulours names' => 'orange;black | green;white',
            'Noms Couleurs normal' => 'jaune;noir | bleu;blanc',
            'Noms Couleurs chromatique' => 'orange;noir | vert;blanc',
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
