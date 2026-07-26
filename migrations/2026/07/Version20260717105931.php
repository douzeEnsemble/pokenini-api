<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717105931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pokemon_image_credit table for per-image source attribution (size, shininess, source name/url)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pokemon_image_credit (size VARCHAR(16) NOT NULL, is_shiny BOOLEAN NOT NULL, source_name VARCHAR(255) DEFAULT NULL, source_url VARCHAR(255) DEFAULT NULL, id UUID NOT NULL, pokemon_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F25B4BEF2FE71C3E ON pokemon_image_credit (pokemon_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_pokemon_image_credit_slot ON pokemon_image_credit (pokemon_id, size, is_shiny)');
        $this->addSql('ALTER TABLE pokemon_image_credit ADD CONSTRAINT FK_F25B4BEF2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon_image_credit DROP CONSTRAINT FK_F25B4BEF2FE71C3E');
        $this->addSql('DROP TABLE pokemon_image_credit');
    }
}
