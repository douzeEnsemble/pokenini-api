<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104081908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX trainer_election_pokemon');
        $this->addSql('ALTER TABLE trainer_pokemon_elo ADD dex_slug VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX trainer_election_pokemon ON trainer_pokemon_elo (trainer_external_id, dex_slug, election_slug, pokemon_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX trainer_election_pokemon');
        $this->addSql('ALTER TABLE trainer_pokemon_elo DROP dex_slug');
        $this->addSql('CREATE UNIQUE INDEX trainer_election_pokemon ON trainer_pokemon_elo (trainer_external_id, election_slug, pokemon_id)');
    }
}
