<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250113053112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trainer_pokemon_elo ADD win_count INT NOT NULL');
        $this->addSql('ALTER TABLE trainer_pokemon_elo RENAME COLUMN count TO view_count');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE trainer_pokemon_elo ADD count INT NOT NULL');
        $this->addSql('ALTER TABLE trainer_pokemon_elo DROP view_count');
        $this->addSql('ALTER TABLE trainer_pokemon_elo DROP win_count');
    }
}
