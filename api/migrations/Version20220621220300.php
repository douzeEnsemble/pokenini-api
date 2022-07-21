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
            'Red, Green, Blue, Yellow' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.redgreenblueyellow",
            'Gold, Silver, Crystal' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.goldsilvercrystal",
            'Ruby, Sapphire, Emerald' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.rubysapphireemerald",
            'RedFire, LeafGreen' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.redfireleafgreen",
            'Diamond, Pearl, Platinium' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.diamondpearlplatinium",
            'Heart Gold, Soul Silver' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.heartgoldsoulsilver",
            'Black, White' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.blackwhite",
            'Black 2, White 2' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.black2white2",
            'X, Y' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.xy",
            'Omega Ruby, Alpha Sapphire' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.omegarubyalphasapphire",
            'Sun, Moon' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.sunmoon",
            'Ultra Sun, Ultra Moon' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.ultrasunultramoon",
            'Let\'s Go Pikachu, Let\'s Go Eevee' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.letgopikachuletsgoevoli",
            'Sword, Shield' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.swordswshield",
            'Brilland Diamond, Shining Pearl' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.brillanddiamonrdshiningpearl",
            'Legend Arceus' => "(p.bankable or p.bankableish) and p.specialForm?.name != 'Event' and ba?.legendarceus",
            'Home' => "(p.bankable or p.bankableish)",
            'Home Shiny' => "(p.bankable or p.bankableish) and p.specialForm == null",
            'Home Pogo' => "(p.bankable or p.bankableish) and p.variantForm == null and p.specialForm == null and p.regionalForm == null",
        ]);

        $gameBundlesAndGeneration = [
            'Red, Green, Blue, Yellow' => '1',
            'Gold, Silver, Crystal' => '2',
            'Ruby, Sapphire, Emerald' => '3',
            'RedFire, LeafGreen' => '3',
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
