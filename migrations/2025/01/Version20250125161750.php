<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250125161750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change dex_slug to dex_id in trainer_pokemon_elo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX trainer_election_pokemon');
        $this->addSql('ALTER TABLE trainer_pokemon_elo ADD dex_id UUID NULL');
        $this->addSql('UPDATE trainer_pokemon_elo SET dex_id = d.id FROM dex AS d WHERE dex_slug = d.slug');
        $this->addSql('ALTER TABLE trainer_pokemon_elo ALTER COLUMN dex_id SET NOT NULL');
        $this->addSql('ALTER TABLE trainer_pokemon_elo DROP dex_slug');
        $this->addSql('ALTER TABLE trainer_pokemon_elo ADD CONSTRAINT FK_C01A9E7744FE8083 FOREIGN KEY (dex_id) REFERENCES dex (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C01A9E7744FE8083 ON trainer_pokemon_elo (dex_id)');
        $this->addSql('CREATE UNIQUE INDEX trainer_election_pokemon ON trainer_pokemon_elo (trainer_external_id, dex_id, election_slug, pokemon_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE trainer_pokemon_elo DROP CONSTRAINT FK_C01A9E7744FE8083');
        $this->addSql('DROP INDEX IDX_C01A9E7744FE8083');
        $this->addSql('DROP INDEX trainer_election_pokemon');
        $this->addSql('ALTER TABLE trainer_pokemon_elo ADD dex_slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE trainer_pokemon_elo DROP dex_id');
        $this->addSql('CREATE UNIQUE INDEX trainer_election_pokemon ON trainer_pokemon_elo (trainer_external_id, dex_slug, election_slug, pokemon_id)');
    }
}
