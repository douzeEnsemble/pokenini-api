<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221026100128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add display template to Dex';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE dex ADD display_template VARCHAR(255) NOT NULL DEFAULT 'box'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dex DROP display_template');
    }
}
