<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250125081923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Election order number to dex';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD election_order_number INT NULL');
        $this->addSql('UPDATE dex set election_order_number = order_number');
        $this->addSql('ALTER TABLE dex ALTER COLUMN election_order_number SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP election_order_number');
    }
}
