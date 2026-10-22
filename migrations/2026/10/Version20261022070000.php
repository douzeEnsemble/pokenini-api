<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Order the dex of the games';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD game_order_number INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE dex SET game_order_number = order_number');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP game_order_number');
    }
}
