<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240217082126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove pokemon.primeName and use pokemon.slug for linking';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP prime_name');
        $this->addSql('ALTER TABLE game_availability RENAME COLUMN pokemon_name TO pokemon_slug');
        $this->addSql('ALTER TABLE game_shiny_availability RENAME COLUMN pokemon_name TO pokemon_slug');
        $this->addSql('ALTER TABLE regional_dex_number RENAME COLUMN pokemon_name TO pokemon_slug');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE regional_dex_number RENAME COLUMN pokemon_slug TO pokemon_name');
        $this->addSql('ALTER TABLE game_availability RENAME COLUMN pokemon_slug TO pokemon_name');
        $this->addSql('ALTER TABLE game_shiny_availability RENAME COLUMN pokemon_slug TO pokemon_name');
        $this->addSql('ALTER TABLE pokemon ADD prime_name VARCHAR(255) NOT NULL');
    }
}
