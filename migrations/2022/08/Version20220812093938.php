<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220812093938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add french translation to dex';
    }

    public function up(Schema $schema): void
    {
        // Add column without NO NULL constraint
        $this->addSql('ALTER TABLE dex ADD french_name VARCHAR(255) NULL');

        // Update LPA names and slugs
        $this->addSql("UPDATE dex SET name = 'Pokémon Legends Arceus', slug = 'pokemonlegendsarceus' WHERE name = 'Legend Arceus'");
        $this->addSql("UPDATE game_bundle SET name = 'Pokémon Legends Arceus', slug = 'pokemonlegendsarceus' WHERE name = 'Legend Arceus'");
        $this->addSql("UPDATE game SET name = 'Pokémon Legends Arceus', slug = 'pokemonlegendsarceus' WHERE name = 'Legend Arceus'");

        $rule = <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and (
                    p.regionalForm === null
                    or p.regionalForm?.name === 'Hisuian'
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name === 'Alpha'
                )
                and ba?.pokemonlegendsarceus
                RULE;
        $this->addSql("UPDATE dex SET selection_rule = :rule WHERE slug = 'pokemonlegendsarceus'", ['rule' => $rule]);

        $dexNames = [
            'Red, Green, Blue, Yellow' => 'Rouge, Vert, Bleu, Jaune',
            'Gold, Silver, Crystal' => 'Or, Argent, Cristal',
            'Ruby, Sapphire, Emerald' => 'Rubis, Saphir, Émeraude',
            'Fire Red, Leaf Green' => 'Rouge Feu, Vert Feuille',
            'Diamond, Pearl, Platinium' => 'Diamant, Perle, Platine',
            'Heart Gold, Soul Silver' => 'Or HeartGold, Argent SoulSilver',
            'Black, White' => 'Noire, Blanche',
            'Black 2, White 2' => 'Noire 2, Blanche 2',
            'X, Y' => 'X, Y',
            'Omega Ruby, Alpha Sapphire' => 'Rubis Oméga, Saphir Alpha',
            'Sun, Moon' => 'Soleil, Lune',
            'Ultra Sun, Ultra Moon' => 'Ultra Soleil, Ultra Lune',
            "Let's Go Pikachu, Let's Go Eevee" => "Let's Go Pikachu, Let's Go Évoli",
            'Sword, Shield' => 'Épée, Bouclier',
            'Brilliant Diamond, Shining Pearl' => 'Diamant Étincelant, Perle Scintillante',
            'Pokémon Legends Arceus' => 'Légendes Pokémon : Arceus',
            'Home' => 'Home',
            'Home Shiny' => 'Home Chromatique',
            'Home Pokemon Go' => 'Home Pokemon Go',
        ];
        $this->addDexFrenchNames($dexNames);

        // Alter column without NO NULL constraint
        $this->addSql('ALTER TABLE dex ALTER french_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP french_name');
    }

    private function addDexFrenchNames(array $dexNames): void
    {
        foreach ($dexNames as $dexName => $dexFrenchName) {
            $this->addSql(
                "UPDATE dex SET french_name = :dexFrenchName WHERE name = :dexName",
                [
                    'dexFrenchName' => $dexFrenchName,
                    'dexName' => $dexName,
                ]
            );
        }
    }
}
