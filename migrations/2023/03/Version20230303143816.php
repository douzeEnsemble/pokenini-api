<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230303143816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is released column for Dex';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD is_released BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE dex ALTER is_released DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP is_released');
    }
}
