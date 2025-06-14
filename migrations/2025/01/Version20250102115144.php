<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250102115144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vote for favorite pokémon entities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE trainer_pokemon_elo (elo INT NOT NULL, trainer_external_id VARCHAR(255) NOT NULL, election_slug VARCHAR(255) NOT NULL, id UUID NOT NULL, pokemon_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C01A9E772FE71C3E ON trainer_pokemon_elo (pokemon_id)');
        $this->addSql('CREATE UNIQUE INDEX trainer_election_pokemon ON trainer_pokemon_elo (trainer_external_id, election_slug, pokemon_id)');
        $this->addSql('CREATE TABLE trainer_vote (trainer_external_id VARCHAR(255) NOT NULL, election_slug VARCHAR(255) NOT NULL, winners TEXT NOT NULL, losers TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE trainer_pokemon_elo ADD CONSTRAINT FK_C01A9E772FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_pokemon_elo DROP CONSTRAINT FK_C01A9E772FE71C3E');
        $this->addSql('DROP TABLE trainer_pokemon_elo');
        $this->addSql('DROP TABLE trainer_vote');
    }
}
