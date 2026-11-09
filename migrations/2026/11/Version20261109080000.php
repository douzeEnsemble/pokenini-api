<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261109080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Keep the names of the Pokémon, dex, dex categories, regions and trainer dex in every language in a single column';
    }

    public function up(Schema $schema): void
    {
        // Each column is filled from the existing ones, which stay as other queries still read them
        $this->addColumns('pokemon', [
            'names' => ['name', 'french_name'],
            'simplified_names' => ['simplified_name', 'simplified_french_name'],
            'forms_labels' => ['forms_label', 'forms_french_label'],
        ]);
        $this->addColumns('dex', [
            'names' => ['name', 'french_name'],
            'descriptions' => ['description', 'french_description'],
        ]);

        foreach (['dex_category', 'region', 'trainer_dex'] as $table) {
            $this->addColumns($table, ['names' => ['name', 'french_name']]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP names, DROP simplified_names, DROP forms_labels');
        $this->addSql('ALTER TABLE dex DROP names, DROP descriptions');

        foreach (['dex_category', 'region', 'trainer_dex'] as $table) {
            $this->addSql("ALTER TABLE {$table} DROP names");
        }
    }

    /**
     * @param array<string, array{0: string, 1: string}> $columns the new column => the English and French ones
     */
    private function addColumns(string $table, array $columns): void
    {
        foreach ($columns as $column => [$english, $french]) {
            $this->addSql("ALTER TABLE {$table} ADD {$column} JSONB DEFAULT '{}' NOT NULL");
            $this->addSql("UPDATE {$table} SET {$column} = jsonb_build_object('en', {$english}, 'fr', {$french})");
        }
    }
}
