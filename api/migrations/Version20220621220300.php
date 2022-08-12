<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\Uid\Uuid;

final class Version20220621220300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set dex, bundle generation and game data';
    }

    public function up(Schema $schema): void
    {
        $this->insertDexes([
            'Red, Green, Blue, Yellow' => <<< RULE
                (p.bankable or p.bankableish)
                and p.variantForm === null
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.redgreenblueyellow
                RULE,
            'Gold, Silver, Crystal' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.goldsilvercrystal
                RULE,
            'Ruby, Sapphire, Emerald' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.rubysapphireemerald
                RULE,
            'Red Fire, Leaf Green' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.redfireleafgreen
                RULE,
            'Diamond, Pearl, Platinium' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.diamondpearlplatinium
                RULE,
            'Heart Gold, Soul Silver' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.heartgoldsoulsilver
                RULE,
            'Black, White' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.blackwhite
                RULE,
            'Black 2, White 2' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.black2white2
                RULE,
            'X, Y' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name in ['Mega']
                )
                and ba?.xy
                RULE,
            'Omega Ruby, Alpha Sapphire' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name in ['Mega']
                )
                and ba?.omegarubyalphasapphire
                RULE,
            'Sun, Moon' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name in ['Mega']
                )
                and (
                    p.regionalForm === null
                    or p.regionalForm?.name === 'Alolan'
                )
                and p.name !== 'Lycanroc-Dusk'
                and ba?.sunmoon
                RULE,
            'Ultra Sun, Ultra Moon' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name in ['Mega']
                )
                and (
                    p.regionalForm === null
                    or p.regionalForm?.name === 'Alolan'
                )
                and ba?.ultrasunultramoon
                RULE,
            'Let\'s Go Pikachu, Let\'s Go Eevee' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Gender']
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name in ['Mega']
                )
                and (
                    p.regionalForm === null
                    or p.regionalForm?.name === 'Alolan'
                )
                and ba?.letsgopikachuletsgoeevee
                RULE,
            'Sword, Shield' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and (
                    p.regionalForm === null
                    or p.regionalForm?.name in ['Alolan', 'Galarian']
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name === 'Gigantamax'
                )
                and ba?.swordshield
                RULE,
            'Brilland Diamond, Shining Pearl' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender']
                )
                and p.regionalForm === null
                and p.specialForm === null
                and ba?.brillanddiamondshiningpearl
                RULE,
            'Legend Arceus' => <<<RULE
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
                and ba?.legendarceus
                RULE,
            'Home' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian', 'Item']
                )
                RULE,
            'Home Shiny' => <<<RULE
                (p.bankable or p.bankableish)
                and (
                    p.variantForm === null
                    or p.variantForm?.name in ['Baby', 'Alternate', 'Gender', 'Therian']
                )
                and (
                    p.specialForm === null
                    or p.specialForm?.name === 'Gigantamax'
                )
                and (
                    p.primeName !== 'Alcremie'
                    or p.name in ['Alcremie-Vanilla-Cream-Strawberry', 'Gigantamax Alcremie-Vanilla-Cream-Strawberry']
                )
                RULE,
            'Home Pokemon Go' => <<<RULE
                (p.bankable or p.bankableish)
                and p.variantForm == null
                and p.specialForm == null
                and p.regionalForm == null
                RULE,
        ]);

        $gameBundlesAndGeneration = [
            'Red, Green, Blue, Yellow' => '1',
            'Gold, Silver, Crystal' => '2',
            'Ruby, Sapphire, Emerald' => '3',
            'Red Fire, Leaf Green' => '3',
            'Diamond, Pearl, Platinium' => '4',
            'Heart Gold, Soul Silver' => '4',
            'Black, White' => '5',
            'Black 2, White 2' => '5',
            'X, Y' => '6',
            'Omega Ruby, Alpha Sapphire' => '6',
            'Sun, Moon' => '7',
            'Ultra Sun, Ultra Moon' => '7',
            'Let\'s Go Pikachu, Let\'s Go Eevee' => '7',
            'Pokemon Go' => '7',
            'Sword, Shield' => '8',
            'Brilland Diamond, Shining Pearl' => '8',
            'Legend Arceus' => '8',
        ];

        $generations = array_unique(array_values($gameBundlesAndGeneration));
        $this->insertGenerations($generations);

        $this->insertGameBundleFromBundlesAndGenerations($gameBundlesAndGeneration);

        $this->insertGamesFromBundles(array_keys($gameBundlesAndGeneration));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE dex CASCADE');
        $this->addSql('TRUNCATE TABLE game_generation CASCADE');
        $this->addSql('TRUNCATE TABLE game_bundle CASCADE');
        $this->addSql('TRUNCATE TABLE game CASCADE');
    }

    private function insertGenerations(array $generations): void
    {
        if (empty($generations)) {
            return;
        }

        $slugify = new Slugify();

        $sqlValues = [];
        $sqlParameters = [];
        $i = 0;
        foreach ($generations as $generation) {
            $sqlValues[] = ":id$i, :name$i, :slug$i";
            $sqlParameters["id$i"] = Uuid::v4();
            $sqlParameters["name$i"] = $generation;

            $sqlParameters["slug$i"] = $slugify->slugify($generation, '');

            $i++;
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $this->addSql("INSERT INTO game_generation (id, name, slug) VALUES ($sqlValuesStr)", $sqlParameters);
    }

    private function insertGameBundleFromBundlesAndGenerations(array $gameBundlesAndGeneration)
    {
        $slugify = new Slugify();

        $i = 0;
        foreach ($gameBundlesAndGeneration as $bundleName => $generationName) {
            $sqlValues[] = ":id$i, :name$i, :slug$i, :orderNumber$i, (SELECT id FROM game_generation WHERE name = :generationName$i)";

            $sqlParameters["id$i"] = Uuid::v4();
            $sqlParameters["name$i"] = $bundleName;
            $sqlParameters["slug$i"] = $slugify->slugify($bundleName, '');
            $sqlParameters["orderNumber$i"] = $i + 1;
            $sqlParameters["generationName$i"] = $generationName;

            $i++;
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $this->addSql("INSERT INTO game_bundle (id, name, slug, order_number, generation_id) VALUES ($sqlValuesStr)", $sqlParameters);
    }

    private function insertGamesFromBundles(array $gameBundlesAndGeneration): void
    {
        $slugify = new Slugify();

        $i = 0;
        foreach ($gameBundlesAndGeneration as $gameBundle) {
            $games = explode(',', $gameBundle);
            $games = array_map('trim', $games);

            foreach ($games as $game) {
                $sqlValues[] = ":id$i, :name$i, :slug$i, :orderNumber$i, (SELECT id FROM game_bundle WHERE name = :bundleName$i)";

                $sqlParameters["id$i"] = Uuid::v4();
                $sqlParameters["name$i"] = $game;
                $sqlParameters["slug$i"] = $slugify->slugify($game, '');
                $sqlParameters["orderNumber$i"] = $i + 1;
                $sqlParameters["bundleName$i"] = $gameBundle;

                $i++;
            }
        }

        $sqlValuesStr = implode('), (', $sqlValues);

        $this->addSql("INSERT INTO game (id, name, slug, order_number, bundle_id) VALUES ($sqlValuesStr)", $sqlParameters);
    }

    private function insertDexes(array $dexes): void
    {
        $slugify = new Slugify();

        $i = 1;
        foreach ($dexes as $dexName => $selectionRule) {
            $sql = <<<SQL
            INSERT INTO dex (id, name, selection_rule, slug, order_number)
            VALUES (gen_random_uuid(), :name, :selectionRule, :slug, :orderNumber)
            SQL;

            $this->addSql($sql, [
                'name' => $dexName,
                'selectionRule' => $selectionRule,
                'slug' => $slugify->slugify($dexName, ''),
                'orderNumber' => $i++,
            ]);
        }
    }
}
