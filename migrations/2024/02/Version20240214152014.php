<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240214152014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add french name for game bundle';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_bundle ADD french_name VARCHAR(255) NULL');

        $this->addSql('UPDATE game_bundle SET french_name = name');

        $this->addSql('ALTER TABLE game_bundle ALTER COLUMN french_name SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_bundle DROP french_name');
    }
}
