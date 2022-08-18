<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220818114156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add display form option to dex';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex ADD is_display_form BOOLEAN NOT NULL DEFAULT TRUE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP is_display_form');
    }
}
