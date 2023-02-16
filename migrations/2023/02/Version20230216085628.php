<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230216085628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove sprites urls from database';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP regular_sprite_url');
        $this->addSql('ALTER TABLE pokemon DROP shiny_sprite_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon ADD regular_sprite_url VARCHAR(255) DEFAULT \'\' NOT NULL');
        $this->addSql('ALTER TABLE pokemon ADD shiny_sprite_url VARCHAR(255) DEFAULT \'\' NOT NULL');
    }
}
