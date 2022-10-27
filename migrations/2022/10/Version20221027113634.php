<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221027113634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sprites url';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon ADD regular_sprite_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE pokemon ADD shiny_sprite_url VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pokemon DROP regular_sprite_url');
        $this->addSql('ALTER TABLE pokemon DROP shiny_sprite_url');
    }
}
