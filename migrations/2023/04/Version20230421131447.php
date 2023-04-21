<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230421131447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Game Shiny Availability table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE game_shiny_availability (id UUID NOT NULL, game_id UUID NOT NULL, pokemon_name VARCHAR(255) NOT NULL, availability VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_57A1BEF6E48FD905 ON game_shiny_availability (game_id)');
        $this->addSql('COMMENT ON COLUMN game_shiny_availability.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN game_shiny_availability.game_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE game_shiny_availability ADD CONSTRAINT FK_57A1BEF6E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_shiny_availability DROP CONSTRAINT FK_57A1BEF6E48FD905');
        $this->addSql('DROP TABLE game_shiny_availability');
    }
}
