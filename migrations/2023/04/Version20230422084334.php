<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230422084334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Game Bundle Shiny Availability table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE game_bundle_shiny_availability (id UUID NOT NULL, pokemon_id UUID NOT NULL, bundle_id UUID NOT NULL, is_available BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_923E348F2FE71C3E ON game_bundle_shiny_availability (pokemon_id)');
        $this->addSql('CREATE INDEX IDX_923E348FF1FAD9D3 ON game_bundle_shiny_availability (bundle_id)');
        $this->addSql('COMMENT ON COLUMN game_bundle_shiny_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_bundle_shiny_availability.pokemon_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_bundle_shiny_availability.bundle_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE game_bundle_shiny_availability ADD CONSTRAINT FK_923E348F2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_bundle_shiny_availability ADD CONSTRAINT FK_923E348FF1FAD9D3 FOREIGN KEY (bundle_id) REFERENCES game_bundle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_bundle_shiny_availability DROP CONSTRAINT FK_923E348F2FE71C3E');
        $this->addSql('ALTER TABLE game_bundle_shiny_availability DROP CONSTRAINT FK_923E348FF1FAD9D3');
        $this->addSql('DROP TABLE game_bundle_shiny_availability');
    }
}
