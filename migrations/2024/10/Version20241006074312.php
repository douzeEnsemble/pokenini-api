<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241006074312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove dex.is_private';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP is_private');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD is_private BOOLEAN NOT NULL');
    }
}
