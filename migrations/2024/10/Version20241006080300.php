<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241006080300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dex.is_premium';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD is_premium BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE dex ALTER COLUMN is_premium DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP is_premium');
    }
}
