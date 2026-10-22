<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Order the dex of each game';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD color_game_order_number INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE dex ADD shiny_game_order_number INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE dex ADD which_shiny_game_order_number INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE dex SET color_game_order_number = order_number, shiny_game_order_number = order_number, which_shiny_game_order_number = order_number');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP color_game_order_number');
        $this->addSql('ALTER TABLE dex DROP shiny_game_order_number');
        $this->addSql('ALTER TABLE dex DROP which_shiny_game_order_number');
    }
}
